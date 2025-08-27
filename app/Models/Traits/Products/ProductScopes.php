<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait ProductScopes
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('is_approved', false);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeOnDiscount(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->whereNotNull('discount_price')
            ->where(function ($q) use ($now) {
                $q->whereNull('discount_start')
                  ->orWhere('discount_start', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('discount_end')
                  ->orWhere('discount_end', '>=', $now);
            });
    }

    public function scopeByCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('barcode', $term)
              ->orWhereHas('tags', function ($tagQuery) use ($term) {
                  $tagQuery->where('name', 'like', "%{$term}%");
              });
        });
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        return $query
            ->when($filters['category'] ?? false, fn($q, $category) =>
                $q->whereHas('category', fn($cat) =>
                    $cat->where('id', $category)
                        ->orWhere('slug', $category)
                )
            )
            ->when($filters['search'] ?? false, fn($q, $search) =>
                $q->search($search)
            )
            ->when($filters['min_price'] ?? false, fn($q, $min) =>
                $q->where('price', '>=', $min)
            )
            ->when($filters['max_price'] ?? false, fn($q, $max) =>
                $q->where('price', '<=', $max)
            )
            ->when($filters['in_stock'] ?? false, fn($q) =>
                $q->inStock()
            )
            ->when($filters['is_featured'] ?? false, fn($q) =>
                $q->featured()
            )
            ->when($filters['sort_by'] ?? false, fn($q, $sortBy) =>
                $q->orderBy($sortBy, $filters['sort_order'] ?? 'desc')
            );
    }

    public function scopePopular(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('sold_count', 'desc')->limit($limit);
    }

    public function scopeTopRated(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('rating_cache', 'desc')
                     ->orderBy('review_count', 'desc')
                     ->limit($limit);
    }

    public function scopeDigital(Builder $query): Builder
    {
        return $query->where('is_virtual', true);
    }

    public function scopePhysical(Builder $query): Builder
    {
        return $query->where('is_virtual', false);
    }
}