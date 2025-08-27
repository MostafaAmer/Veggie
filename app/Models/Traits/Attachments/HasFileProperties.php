<?php

namespace App\Models\Traits;

trait HasFileProperties
{
    public function getFileSizeFormattedAttribute(): string
    {
        return number_format($this->attributes['file_size'] / 1024, 2) . ' KB';
    }

    public function getDimensionsAttribute(): ?array
    {
        if (! $this->is_image) {
            return null;
        }

        return json_decode($this->attributes['dimension'], true);
    }
}