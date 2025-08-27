<?php
namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait HasCouponScopes
{
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeValid(Builder $q): Builder
    {
        $now = Carbon::now();
        return $q->active()
                 ->where(fn($q) => $q->whereNull('valid_from')
                                     ->orWhere('valid_from', '<=', $now))
                 ->where(fn($q) => $q->whereNull('valid_to')
                                     ->orWhere('valid_to', '>=', $now))
                 ->where(fn($q) => $q->whereNull('max_uses')
                                     ->orWhereColumn('used_count', '<', 'max_uses'));
    }

    public function scopeByCode(Builder $q, string $code): Builder
    {
        return $q->where('code',$code);
    }

    public function scopeForUser(Builder $q, string $userId): Builder
    {
        return $q->whereNull('user_id')
                 ->orWhere('user_id',$userId);
    }
}
