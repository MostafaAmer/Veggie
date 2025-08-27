<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphMany};
use App\Models\Traits\{
    HasUuidAndSlug,
    HasStockManagement,
    HasPricingAndDiscounts,
    HasReviewStatistics,
    HasAttachmentsRelations,
    ProductScopes
};


class Product extends Model
{
    use HasFactory, SoftDeletes;
    use HasUuidAndSlug, HasStockManagement, HasPricingAndDiscounts, HasReviewStatistics,
         HasAttachmentsRelations, ProductScopes;


    protected $fillable = [
        'uuid', 'slug', 'name', 'description',
        'price', 'discount_price', 'discount_start', 'discount_end',
        'stock', 'min_order_quantity', 'max_order_quantity',
        'dimensions', 'weight',
        'category_id', 'created_by',
        'is_active', 'is_approved', 'is_featured',
        'meta',
    ];


    protected $casts = [
        'price'                 => 'decimal:2',
        'cost_price'            => 'decimal:2',
        'discount_price'        => 'decimal:2',
        'weight'                => 'decimal:2',
        'stock'                 => 'integer',
        'sold_count'            => 'integer',
        'min_order_quantity'    => 'integer',
        'max_order_quantity'    => 'integer',
        'is_active'             => 'boolean',
        'is_featured'           => 'boolean',
        'discount_start'        => 'datetime',
        'discount_end'          => 'datetime',
        'is_approved'           => 'boolean',
        'rating_cache'          => 'float',
        'review_count'          => 'integer'
    ];

    protected $with = ['mainImage'];

    protected $appends = ['final_price', 'discount_percentage', 'can_order', 'is_out_of_stock'];

    public function category(): BelongsTo
    {
         return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)
                    ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}