<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)
                  ->unique()
                  ->index()
                  ->comment('Unique coupon code');
            $table->enum('type', ['percentage', 'fixed', 'free_shipping', 'bogo'])
                  ->index()
                  ->comment('Type of discount');
            $table->decimal('value', 10, 2)
                  ->comment('Discount value: percentage or fixed amount');
            $table->decimal('min_order_value', 12, 2)
                  ->default(0)
                  ->comment('Minimum order amount to apply coupon');
            $table->decimal('max_discount_amount', 12, 2)
                  ->nullable()
                  ->comment('Caps the discount for percentage coupons');
            $table->decimal('max_uses')
                  ->nullable()
                  ->comment('Total uses allowed');
            $table->decimal('used_count')
                  ->default(0)
                  ->comment('Times this coupon has been used');
            $table->unsignedInteger('max_uses_per_user')
                  ->nullable()
                  ->comment('Limit per individual user');
            $table->boolean('is_reusable')
                  ->default(false)
                  ->comment('Can a user reuse after first redemption?');
            $table->timestampTz('valid_from')
                  ->nullable()
                  ->comment('Start datetime for validity');
            $table->timestampTz('valid_to')
                  ->nullable()
                  ->index()
                  ->comment('End datetime for validity');
            $table->boolean('is_active')
                  ->default(true)
                  ->index()
                  ->comment('Active flag');
            $table->enum('scope', ['global', 'category', 'product'])
                  ->default('global')
                  ->index()
                  ->comment('Where the coupon can be applied');

            $table->foreignUuid('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('User who created this coupon');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('coupon_user', function (Blueprint $table) {
            $table->foreignUuid('coupon_id')
                  ->constrained('coupons')
                  ->cascadeOnDelete();
            $table->foreignUuid('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->unsignedInteger('usage_count')
                  ->default(0)
                  ->comment('Times this user used the coupon');
            $table->timestamps();

            $table->primary(['coupon_id', 'user_id']);
        });

        Schema::create('coupon_category', function (Blueprint $table) {
            $table->foreignUuid('coupon_id')
                  ->constrained('coupons')
                  ->cascadeOnDelete();
            $table->foreignUuid('category_id')
                  ->constrained('categories')
                  ->cascadeOnDelete();

            $table->primary(['coupon_id', 'category_id']);
        });

        Schema::create('coupon_product', function (Blueprint $table) {
            $table->foreignUuid('coupon_id')
                  ->constrained('coupons')
                  ->cascadeOnDelete();
            $table->foreignUuid('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            $table->primary(['coupon_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupon_category');
        Schema::dropIfExists('coupon_user');
        Schema::dropIfExists('coupons');
    }
};