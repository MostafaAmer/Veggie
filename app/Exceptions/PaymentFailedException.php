<?php

namespace App\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    /**
     * إنشاء رسالة مخصصة عند الفشل
     *
     * @param  string|null  $message
     * @param  int          $code
     */
    public function __construct(
        string $message = 'Payment processing failed.',
        int $code = 0
    ) {
        parent::__construct($message, $code);
    }
}