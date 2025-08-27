<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSucceeded
{
    use Dispatchable, SerializesModels;

    public Payment $payment;
    public array   $gatewayResponse;

    /**
     * PaymentSucceeded constructor.
     *
     * @param  Payment  $payment
     * @param  array    $gatewayResponse
     */
    public function __construct(Payment $payment, array $gatewayResponse = [])
    {
        $this->payment         = $payment;
        $this->gatewayResponse = $gatewayResponse;
    }
}