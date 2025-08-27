<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundCompleted
{
    use Dispatchable, SerializesModels;

    public Payment $payment;
    public array   $gatewayResponse;

    /**
     * Create a new event instance.
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