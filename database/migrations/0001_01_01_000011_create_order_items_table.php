<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('order_id');
            $table->uuid('product_id');
            $table->uuid('category_id')->nullable();

            $table->string('product_name');
            $table->string('product_image')->nullable(); 
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12, 2); 
            $table->decimal('original_price', 12, 2); 
            $table->decimal('discount', 12, 2)->default(0); 
            $table->decimal('total', 12, 2); 
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('dimensions')->nullable();

            $table->json('attributes')->nullable(); 
            $table->json('custom_options')->nullable(); 
            $table->text('notes')->nullable();
            $table->string('sku')->nullable()->comment('Product SKU at time of purchase');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('Tax amount for this item');
            $table->decimal('refunded_amount', 10, 2)->default(0)->comment('Refunded amount for this item');

            $table->enum('status', [
                'pending',
                'confirmed',
                'shipped',
                'delivered',
                'returned',
                'refunded'
            ])->default('pending');
            
            $table->timestamps();

            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
                  
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');

            $table->foreign('category_id')
                  ->references('id')->on('categories')
                  ->onDelete('set null');
                  
            $table->index(
                ['order_id', 'product_id', 'status'],
                'order_items_order_product_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
