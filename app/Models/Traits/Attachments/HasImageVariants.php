<?php

namespace App\Models\Traits;
use Illuminate\Support\Facades\Storage; 

trait HasImageVariants
{
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->is_image) {
            return null;
        }

        $thumbPath = 'thumbnails/' . basename($this->path);
        return $this->disk
            ? Storage::disk($this->disk)->url($thumbPath)
            : null;
    }

    public function getResponsiveImageUrlsAttribute(): ?array
    {
        if (! $this->is_image) {
            return null;
        }

        $sizes = [300, 600, 1200];
        return collect($sizes)
            ->mapWithKeys(fn($w) => [
                $w => Storage::disk($this->disk)->url("resized/{$w}/" . basename($this->path))
            ])->toArray();
    }
}