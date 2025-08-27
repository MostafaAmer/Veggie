<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasUuidAndSlug
{
    protected static function bootHasUuidAndSlug(): void
    {
        static::creating(function ($model) {
            $model->id = $model->id ?? (string) Str::orderedUuid();

            if (empty($model->slug)) {
                $base = Str::slug($model->name);
                $slug = $base;
                $count = $model::where('slug', 'like', "{$base}%")->count();
                if ($count > 0) {
                    $slug .= "-{$count}";
                }
                $model->slug = $slug;
            }

            $model->is_active   = $model->is_active ?? true;
            $model->is_approved = $model->is_approved ?? false;
            $model->stock       = $model->stock ?? 0;
        });
    }
}