<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

abstract class BaseModel extends Model
{
    use SoftDeletes;

    protected $keyType     = 'string';
    public    $incrementing = false;

    protected static function booted(): void
    {
        static::creating(fn($model) => $model->{$model->getKeyName()} ??= (string) Str::uuid());
    }
}