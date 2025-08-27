<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Relations\{MorphMany, BelongsTo};
use App\Models\Attachment;
use Illuminate\Support\Collection;

trait HasAttachmentsRelations
{
    public function mainImage(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'main_image_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function productImages(): MorphMany
    {
        return $this->attachments()->where('type', 'product_image');
    }

    public function getImageUrlsAttribute(): array
    {
        return $this->productImages->pluck('url')->toArray();
    }

    public function getMainImageUrlAttribute(): ?string
    {
        return $this->mainImage?->url
            ?? $this->productImages()->first()?->url
            ?? asset('images/default-product.png');
    }

    public function getApplicableItemsAttribute(): Collection
    {
        return match ($this->scope->value) {
            'category' => $this->categories,
            'product'  => $this->products,
            default    => collect(),
        };
    }
}