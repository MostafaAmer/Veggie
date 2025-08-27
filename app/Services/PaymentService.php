<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use App\Events\PaymentCreated;
use App\Events\PaymentSucceeded;
use App\Events\PaymentFailed;
use App\Events\RefundInitiated;
use App\Events\RefundCompleted;
Use App\Enums\PaymentStatus;
use App\Factories\GatewayFactory;

class PaymentService
{
    public function __construct(private GatewayFactory $factory) {}


    public function process(Order $order, array $data): array
    {
        try {
            $payment = Payment::create([
                'order_id'        => $order->id,
                'user_id'         => $order->user_id,
                'amount'          => $order->total,
                'currency'        => $order->currency,
                'method'          => $order->payment_method,
                'status'          => PaymentStatus::Pending,
                'payment_details' => $data['payment_details'] ?? [],
            ]);

            event(new PaymentCreated($payment));

            $gateway  = $this->factory->make($payment->method_enum);
            $response = $gateway->charge($payment, $data);

            $payment->markAsCompleted($response['transaction_id'] ?? null);
            $order->markAsPaid();
            event(new PaymentSucceeded($payment, $response));

            DB::commit();

            return ['success' => true] + $response;
        } catch (\Throwable $e) {
            DB::rollBack();

            $payment->markAsFailed($e->getMessage());
            $order->markAsPaymentFailed();
            event(new PaymentFailed($payment, $e));

            return [
                'success' => false,
                'error'   => 'فشل في عملية الدفع',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ];
        }
    }

    public function refund(Payment $payment, ?float $amount = null): array
    {
        if (! $payment->isRefundable()) {
            return ['success' => false, 'error' => 'لا يمكن استرداد هذا الدفع'];
        }

        $refundAmount = $amount ?? $payment->amount;
        $gateway      = $this->factory->make($payment->method_enum);

        try {
            event(new RefundInitiated($payment, $refundAmount));
            $response = $gateway->refund($payment, $refundAmount);

            $payment->initiateRefund($refundAmount);
            event(new RefundCompleted($payment, $response));

            return ['success' => true] + $response;
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'error'   => 'فشل في عملية الاسترداد',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ];
        }
    }
}