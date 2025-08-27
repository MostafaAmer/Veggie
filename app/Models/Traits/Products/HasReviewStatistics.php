<?php

namespace App\Models\Traits;

trait HasReviewStatistics
{
    public function getAverageRatingAttribute(): ?float
    {
        return $this->rating_cache ?? $this->reviews()->avg('rating');
    }

    public function updateRating(): void
    {
        $this->update([
            'rating_cache' => $this->reviews()->avg('rating'),
            'review_count' => $this->reviews()->count()
        ]);
    }
}