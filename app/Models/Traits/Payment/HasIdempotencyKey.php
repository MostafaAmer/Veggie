<?php
declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasIdempotencyKey
{
    public static function bootHasIdempotencyKey(): void
    {
        static::creating(function ($model) {
            if (empty($model->idempotency_key)) {
                $model->idempotency_key = (string) Str::uuid();
            }
        });
    }
}