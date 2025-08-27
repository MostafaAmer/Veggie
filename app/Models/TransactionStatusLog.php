<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TransactionStatus;
use Illuminate\Support\Str; 

class TransactionStatusLog extends Model
{
    public $incrementing = false;
    public $keyType     = 'string';
    public $timestamps  = false;

    protected $fillable = [
        'id', 'transaction_id', 'changed_by', 'status', 'action', 'details', 'changed_at'
    ];

    protected $casts = [
        'status'     => TransactionStatus::class,
        'details'    => 'array',
        'changed_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::creating(function (self $log) {
            $log->id         = (string) Str::uuid();
            $log->changed_at = $log->changed_at ?? now();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeForTransaction($query, $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute(): string
    {
        return TransactionStatus::getDescription($this->status) ?? ucfirst($this->status);
    }
}