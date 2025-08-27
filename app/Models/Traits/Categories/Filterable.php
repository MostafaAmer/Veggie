<?php
namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null,
                fn($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->when($filters['status'] ?? null,
                fn($q, $status) => $q->where('status', $status))
            ->when($filters['parent_id'] ?? null,
                fn($q, $id) => $q->where('parent_id', $id))
            ->when(isset($filters['featured']),
                fn($q) => $q->where('is_featured', true))
            ->when(isset($filters['visible']) && $filters['visible'],
                fn($q) => $q->visible());
    }
}