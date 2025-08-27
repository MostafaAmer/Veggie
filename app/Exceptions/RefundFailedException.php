<?php

namespace App\Exceptions;

use Exception;

class RefundFailedException extends Exception
{
    /**
     * إنشاء رسالة مخصصة عند فشل الاسترجاع
     *
     * @param  string|null  $message
     * @param  int          $code
     */
    public function __construct(
        string $message = 'Refund processing failed.',
        int $code = 0
    ) {
        parent::__construct($message, $code);
    }
}