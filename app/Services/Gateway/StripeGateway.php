<?php
namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Stripe\StripeClient;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\RefundFailedException;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(private StripeClient $stripe) {}

    public function charge(Payment $payment, array $data): array
    {
        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount'              => $this->toMinorUnit($payment->amount),
                'currency'            => strtolower($payment->currency),
                'payment_method'      => $data['payment_method_id'],
                'confirmation_method' => 'manual',
                'confirm'             => true,
                'metadata'            => [
                    'order_id' => $payment->order_id,
                    'payment_id' => $payment->id,
                ],
            ]);

            if ($intent->status !== 'succeeded') {
                throw new PaymentFailedException("Stripe status {$intent->status}");
            }

            return $intent->toArray();
        } catch (\Throwable $e) {
            throw new PaymentFailedException($e->getMessage(), 0, $e);
        }
    }

    public function refund(Payment $payment, float $amount): array
    {
        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $payment->transaction_id,
                'amount'         => $this->toMinorUnit($amount),
            ]);

            return $refund->toArray();
        } catch (\Throwable $e) {
            throw new RefundFailedException($e->getMessage(), 0, $e);
        }
    }

    private function toMinorUnit(float $amount): int
    {
        return (int) round($amount * 100);
    }
}