<?php

namespace App\Events;

use App\Models\Payment;
use Throwable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public Payment   $payment;
    public Throwable $exception;

    /**
     * PaymentFailed constructor.
     *
     * @param  Payment    $payment
     * @param  Throwable  $exception
     */
    public function __construct(Payment $payment, Throwable $exception)
    {
        $this->payment   = $payment;
        $this->exception = $exception;
    }
}