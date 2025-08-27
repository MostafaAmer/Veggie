<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->string('reference_number')->unique();

            $table->uuid('user_id');
            $table->uuid('coupon_id')->nullable();

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 10, 8)->nullable();
            $table->json('address')->nullable(); 
            
            $table->enum('status', [
                'pending', 
                'confirmed', 
                'processing', 
                'shipped', 
                'delivered', 
                'cancelled',
                'refunded'
            ])->default('pending');

            $table->enum('payment_method', [
                'cash_on_delivery', 
                'credit_card', 
                'wallet'
            ])->default('credit_card');

            $table->boolean('is_paid')->default(false);
            $table->string('payment_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('estimated_delivery_time')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->text('cancellation_reason')->nullable();
            $table->uuid('cancelled_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->string('tracking_number')->nullable()->comment('Shipping tracking number');
            $table->string('shipping_carrier')->nullable()->comment('Shipping company');
            $table->json('shipping_details')->nullable()->comment('Shipping details');
            $table->decimal('refunded_amount', 10, 2)->default(0)->comment('Total refunded amount');

            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('coupon_id')
                  ->references('id')
                  ->on('coupons')
                  ->onDelete('set null');
                  
            $table->foreign('cancelled_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            
            $table->index(['status', 'payment_method', 'is_paid']);
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};