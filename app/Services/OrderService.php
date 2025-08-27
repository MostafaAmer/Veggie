<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\DiscountService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use App\Exceptions\InsufficientStockException;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Events\OrderStatusChanged;
use App\Events\OrderCreated;
use App\Enums\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(
        protected InventoryService      $inventoryService,
        protected DiscountService       $discountService,
        protected PaymentService        $paymentService,
        protected NotificationService   $notificationService,
    ) {}

    public function getAllOrders(): LengthAwarePaginator
    {
        return Order::with(['user:id,name', 'items.product.mainImage'])
            ->latest()
            ->paginate(15);
    }
    public function getUserOrders(int $userId): LengthAwarePaginator
    {
        return Order::with(['items.product.mainImage'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10);
    }

    public function createOrder(User $user, array $data): Order
    {
        $this->validateProductsAvailability($data['items']);

        return DB::transaction(function () use ($user, $data) {
            $this->inventoryService->adjust($data['items'], 'decrease');

            $subtotal       = $this->calculateSubtotal($data['items']);
            $couponId       = $this->resolveCouponId($data['coupon_code'] ?? null);
            $discounts      = $this->discountService->applyDiscounts($data['items'], $data['coupon_code'] ?? null);
            $totalDiscount  = array_sum($discounts);
            $deliveryFee    = $data['delivery_fee'] ?? 0;
            $total          = $subtotal - $totalDiscount + $deliveryFee;
            
           $order = Order::create([
                'user_id'                 => $user->id,
                'address_id'              => $data['address_id'] ?? null,
                'address'                 => $data['address'],
                'coupon_id'               => $couponId,
                'subtotal'                => $subtotal,
                'discount'                => $totalDiscount,
                'delivery_fee'            => $deliveryFee,
                'total'                   => $total,
                'payment_method'          => $data['payment_method'],
                'status'                  => OrderStatus::Pending->value,
                'estimated_delivery_time' => now()->addDays(
                    config('orders.default_delivery_days', 3)),
            ]);

            $this->addOrderItems($order, $data['items']);
            $this->logStatusChange($order, OrderStatus::Pending->value, $user);

            OrderCreated::dispatch($order);
            OrderStatusChanged::dispatch($order, OrderStatus::Pending->value);

            if ($order->payment_method !== 'cash_on_delivery') {
                $this->paymentService->charge($user, $order);
            }

            return $order;
        });
    }

    public function updateOrderStatus(
        Order  $order,
        string $status,
        ?string $notes,
        User   $changedBy
    ): Order {
        $updated = DB::transaction(fn() => tap($order)->changeStatus($status, $notes, $changedBy)->fresh());

        OrderStatusChanged::dispatch($updated, $status, $notes);
        return $updated;
    }

    public function cancelOrder(Order $order, User $cancelledBy, ?string $reason): Order
    {
        $canceled = DB::transaction(function () use ($order, $cancelledBy, $reason) {
            $order->cancel($reason, $cancelledBy);
            $this->inventoryService->adjust(
                $order->items->toArray(),
                'increase');

            if ($order->is_paid) {
                $this->processRefund($order, $order->total, $reason);
            }

            return $order->fresh();
        });

        OrderStatusChanged::dispatch($canceled, OrderStatus::Cancelled->value, $reason);
        return $canceled;
    }

    public function returnOrder(Order $order, string $reason, User $processedBy): Order
    {
        $returned = DB::transaction(function () use ($order, $reason, $processedBy) {
            $order->returnOrder($reason, $processedBy);
            $this->inventoryService->adjust(
                $order->items->toArray(),
                'increase');
            return $order->fresh();
        });

        OrderStatusChanged::dispatch($returned, OrderStatus::Returned->value, $reason);
        return $returned;
    }

    public function refundOrder(Order $order, string $reason, User $processedBy): Order
    {
        $refunded = DB::transaction(function () use ($order, $reason, $processedBy) {
            $order->refundOrder($reason, $processedBy);

            if ($order->is_paid) {
                $this->processRefund($order, $order->total, $reason);
            }

            return $order->fresh();
        });

        OrderStatusChanged::dispatch($refunded, OrderStatus::Refunded->value, $reason);
        return $refunded;
    }

    public function updateShippingDetails(Order $order, array $data): Order
    {
        $updated = DB::transaction(function () use ($order, $data) {
            $order->update([
                'tracking_number'        => $data['tracking_number'],
                'shipping_carrier'       => $data['shipping_carrier'],
                'shipping_details'       => $data['shipping_details'],
                'estimated_delivery_time'=> $data['estimated_delivery'],
            ]);

            return $order->fresh();
        });

        OrderStatusChanged::dispatch($updated,OrderStatus::Shipped->value,$data['tracking_number']);
        return $updated;
    }

    public function generateInvoice(Order $order)
    {
        $order->load(['items.product', 'user', 'address']);

        $pdf = PDF::loadView('invoices.order', compact('order'));
        return $pdf->download("invoice-{$order->id}.pdf");
    }

    public function getOrderStatistics(array $filters = []): array
    {
        $baseQuery = Order::query();
        $filtered  = (clone $baseQuery);

        if (!empty($filters['start_date'])) {
            $filtered->where('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $filtered->where('created_at', '<=', $filters['end_date']);
        }

        return [
            'total_orders'     => $filtered->count(),
            'total_sales'      => $filtered->sum('total'),
            'pending_orders'   => (clone $filtered)->where('status', OrderStatus::Pending->value)->count(),
            'completed_orders' => (clone $filtered)->where('status', OrderStatus::Delivered->value)->count(),
        ];
    }

    protected function validateProductsAvailability(array $items): void
    {
        $ids      = array_column($items, 'product_id');
        $products = Product::whereIn('id', $ids)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($items as $i) {
            $prod = $products->get($i['product_id']);
            if (! $prod || $prod->stock < $i['quantity']) {
                throw new InsufficientStockException(
                    "The required quantity is not available for the product: {$i['product_id']}"
                );
            }
        }
    }

    protected function logStatusChange(
        Order  $order,
        string $status,
        User   $by,
        ?string $notes = null
    ): void {
        OrderStatusLog::create([
            'order_id'   => $order->id,
            'status'     => $status,
            'changed_by' => $by->id,
            'notes'      => $notes,
        ]);
    }

    protected function addOrderItems(Order $order, array $items): void
    {
        $rows = [];
        foreach ($items as $i) {
            $rows[] = [
                'order_id'   => $order->id,
                'product_id' => $i['product_id'],
                'variant_id' => $i['variant_id'] ?? null,
                'quantity'   => $i['quantity'],
                'price'      => $i['price'],
                'total'      => $i['price'] * $i['quantity'],
                'attributes' => $i['attributes'] ?? [],
                'custom_options' => $i['custom_options'] ?? [],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        OrderItem::insert($rows);
    }

    protected function processRefund(Order $order, float $amount, string $reason): void
    {
        Transaction::create([
            'order_id' => $order->id,
            'user_id'  => $order->user_id,
            'amount'   => $amount,
            'type'     => 'refund',
            'status'   => 'completed',
            'notes'    => $reason,
        ]);

        if ($order->payment_method !== 'cash_on_delivery') {
            $this->paymentService->refund($order->payment_id, $amount, $reason);
        }
    }

    protected function getCouponId(?string $couponCode): ?int
    {
        if (!$couponCode) {
            return null;
        }

        return Coupon::where('code', $couponCode)
            ->where('expires_at', '>=', now())
            ->whereColumn('used_count', '<', 'usage_limit')
            ->value('id');
    }

    protected function calculateSubtotal(array $items): float
    {
        return array_reduce($items, fn($sum, $i) => $sum + ($i['price'] * $i['quantity']), 0.0);
    }
}