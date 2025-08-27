<?php
namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });

        static::saving(function ($model) {
            if ($model->isDirty('name')) {
                $base = Str::slug($model->name);
                $exists = static::where('slug', $base)->exists();
                $model->slug = $exists
                    ? "{$base}-" . Str::random(6)
                    : $base;

            }
        });
    }
}