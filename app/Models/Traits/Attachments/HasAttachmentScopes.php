<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Enums\AttachmentType;

trait HasAttachmentScopes
{
    public function scopeImages(Builder $q): Builder
    {
        return $q->where('mime_type', 'like', 'image/%');
    }

    public function scopeVideos(Builder $q): Builder
    {
        return $q->where('mime_type', 'like', 'video/%');
    }

    public function scopeByType(Builder $q, AttachmentType $type): Builder
    {
        return $q->where('type', $type);
    }

    public function scopeRecent(Builder $q): Builder
    {
        return $q->latest('created_at');
    }
}