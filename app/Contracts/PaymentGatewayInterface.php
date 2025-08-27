<?php
namespace App\Contracts;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Charge the customer.
     *
     * @param  Payment  $payment
     * @param  array    $data
     * @return array    gateway response data
     *
     * @throws \App\Exceptions\PaymentFailedException
     */
    public function charge(Payment $payment, array $data): array;

    /**
     * Refund a payment.
     *
     * @param  Payment  $payment
     * @param  float    $amount
     * @return array    gateway response data
     *
     * @throws \App\Exceptions\RefundFailedException
     */
    public function refund(Payment $payment, float $amount): array;
}