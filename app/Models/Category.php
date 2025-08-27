<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany,MorphMany, MorphOne};
use Illuminate\Database\Eloquent\Builder;
use App\Enums\{CategoryStatus, AttachmentType};
use App\Models\Traits\{Filterable, HasCoverImage, HasSlug};
use Illuminate\Support\Collection;

class Category extends Model
{
    use HasFactory, HasSlug, Filterable, HasCoverImage;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'is_active',
        'is_featured',
        'parent_id',
        'order',
        'created_by',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'color',
    ];

    protected $casts = [
        'status'           => CategoryStatus::class,
        'is_active'        => 'boolean',
        'is_featured'      => 'boolean',
        'order'            => 'integer',
        'meta_keywords'    => 'array',
        'deleted_at'       => 'datetime',
    ];

    protected $appends = [
        'cover_image_url',
        'full_path',
        'product_count',
    ];

     /*-----------------------------------
     | Relationships
     *----------------------------------*/

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    public function allProducts(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
                    ->orderBy('order');
    }

    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function coverImage(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable')
                    ->where('type', AttachmentType::Cover);
    }


     /*-----------------------------------
     | Accessors
     *----------------------------------*/

    public function getFullPathAttribute(): string
    {
        $names  = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $names);
    }

    public function getProductCountAttribute(): int
    {
        return $this->products()->count();
    }

    public function getApplicableItemsAttribute(): Collection
    {
        return match ($this->scope->value) {
            'category' => $this->categories,
            'product'  => $this->products,
            default    => collect(),
        };
    }

    /*-----------------------------------
     | Local Scopes
     *----------------------------------*/

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('order', $direction);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', CategoryStatus::Active)
                     ->whereHas('products');
    }
}