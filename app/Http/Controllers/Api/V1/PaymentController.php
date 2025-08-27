<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Requests\RefundPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function process(ProcessPaymentRequest $request, Order $order): JsonResponse
    {
        $this->authorize('process', $order);

        $result = $this->paymentService->processPayment(
            $order,
            $request->validated()
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'] ?? null,
            'payment' => new PaymentResource($order->fresh()->payment),
            'error'   => $result['error']   ?? null,
            'details' => $result['details'] ?? null,
        ], $result['success'] ? 200 : 400);
    }

    public function refund(RefundPaymentRequest $request, Payment $payment): JsonResponse
    {
        $this->authorize('refund', $payment);

        $result = $this->paymentService->refundPayment(
            $payment,
            $request->input('amount')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'تمت عملية الاسترداد بنجاح'
                : null,
            'error'   => $result['error']   ?? null,
            'details' => $result['details'] ?? null,
        ], $result['success'] ? 200 : 400);
    }
}