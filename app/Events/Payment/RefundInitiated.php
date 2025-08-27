<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundInitiated
{
    use Dispatchable, SerializesModels;

    public Payment $payment;
    public float   $amount;

    /**
     * Create a new event instance.
     *
     * @param  Payment  $payment
     * @param  float    $amount
     */
    public function __construct(Payment $payment, float $amount)
    {
        $this->payment = $payment;
        $this->amount  = $amount;
    }
}