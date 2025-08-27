<?php
namespace App\Models\Traits;
use Illuminate\Support\Facades\Storage;

trait HasCoverImage
{
    public function getCoverImageUrlAttribute(): string
    {
        if ($this->coverImage) {
            return Storage::url($this->coverImage->path);
        }
        return Storage::url('defaults/categories/default.png');

    }
}