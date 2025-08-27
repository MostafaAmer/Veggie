<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Storage;

trait HasFileStorage
{
    public function getFilePathAttribute(): string
    {
        return $this->attributes['path'];
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function deleteFile(): bool
    {
        return Storage::disk($this->disk)->delete($this->path);
    }
}