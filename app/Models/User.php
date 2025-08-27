<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};
use App\Models\Traits\HasNotificationSettings;
use App\Models\{Address, Payment, Role, Attachment, Category, Product, Coupon, Notification , Order};
use App\Enums\UserProvider;
use App\Enums\UserStatus;
use App\Models\Traits\{UserScopes, UserAccessors, HasRolesAndPermissions};

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use UserScopes, UserAccessors, HasRolesAndPermissions;
    use Notifiable, HasNotificationSettings;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'avatar',
        'phone',
        'timezone',
        'language',
        'last_login_at',
        'last_login_ip',
        'is_verified',
        'is_active',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'provider_id',
        'pivot',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'provider' => UserProvider::class,
        'status' => UserStatus::class
    ];

    protected $with = ['roles'];

    protected $appends = ['avatar_url', 'is_social_login'];


    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
        $this->tokens()->delete();
    }

    public function markAsVerified(): void
    {
        $this->update(['is_verified' => true, 'email_verified_at' => now()]);
    }

     public function paymentMethods()
    {
        return $this->hasMany(Payment::class);
    }
   
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    public function createdCategories()
    {
        return $this->hasMany(Category::class, 'created_by');
    }

    public function createdProducts()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function cancelledOrders()
    {
        return $this->hasMany(Order::class, 'cancelled_by');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}