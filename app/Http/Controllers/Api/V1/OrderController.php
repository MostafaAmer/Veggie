<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\ReturnOrderRequest;
use Illuminate\Http\Response;
use App\Http\Requests\RefundOrderRequest;
use App\Http\Requests\UpdateShippingRequest;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('can:view_any,App\Models\Order')->only('index');
        $this->middleware('can:view,order')->only('show');
    }

    public function index(): AnonymousResourceCollection
    {
        $user = auth()->user();
        $orders = $user->can('manage_orders')
            ? $this->orderService->getAllOrders()
            : $this->orderService->getUserOrders($user->id);
            
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['address'] = $request->user()
            ->addresses()
            ->findOrFail($data['address_id'])
            ->toArray();

        $order = $this->orderService->createOrder(
            $request->user(),
            $data
        );

        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order'   => new OrderResource($order),
        ], Response::HTTP_CREATED);
    }

    public function show(Order $order): OrderResource
    {
        $this->authorize('view', $order);
        
        $order->load([
            'items.product.mainImage',
            'items.variant',
            'statusLogs.user',
            'coupon',
            'user:id,name,email',
            'transactions',
        ]);

        return new OrderResource($order);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);
        
        $updatedOrder = $this->orderService->updateOrderStatus(
            $order,
            $request->input('status'),
            $request->input('notes', ''),
            $request->user()
        );
        
        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => new OrderResource($updatedOrder)
        ]);
    }

    public function cancel(Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);
        
        $canceled = $this->orderService->cancelOrder(
            $order,
            auth()->user(),
            request()->input('reason', '')
        );
        
        return response()->json([
            'message' => 'The request was successfully cancelled.',
            'order'   => new OrderResource($canceled),
        ]);
    }

    public function return(ReturnOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('return', $order);
        
        $returned = $this->orderService->returnOrder(
            $order,
            $request->input('reason', ''),
            $request->user()
        );
        
        return response()->json([
            'message' => 'Your return request has been successfully submitted.',
            'order'   => new OrderResource($returned),
        ]);
    }

    public function refund(RefundOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('refund', $order);
        
        $refunded = $this->orderService->refundOrder(
            $order,
            $request->input('reason', ''),
            $request->user()
        );

        return response()->json([
            'message' => 'The amount has been successfully refunded.',
            'order'   => new OrderResource($refunded),
        ]);
    }

    public function updateShipping(UpdateShippingRequest $request, Order $order): JsonResponse
    {
        $this->authorize('updateShipping', $order);
        
        $updated = $this->orderService->updateShippingDetails(
            $order,
            $request->validated()
        );

        return response()->json([
            'message' => 'Shipping data updated successfully',
            'order'   => new OrderResource($updated),
        ]);
    }

    public function invoice(Order $order): Response
    {
        $this->authorize('viewInvoice', $order);
        
        $pdf = $this->orderService->generateInvoice($order);
        
        return response($pdf, Response::HTTP_OK)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                sprintf('inline; filename="invoice_%s.pdf"', $order->reference_number)
            );
    }

    public function statistics(): JsonResponse
    {
        $this->authorize('viewStatistics', Order::class);
        
        $stats = $this->orderService->getOrderStatistics(
            request()->all()
        );
        
        return response()->json($stats);
    }
}