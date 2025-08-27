<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();

           
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->string('barcode')->nullable()->index();

            $table->uuid('category_id')->nullable();
            $table->uuid('main_image_id')->nullable();
            $table->uuid('created_by')->nullable();

            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
             $table->unsignedInteger('min_order_quantity')->default(1);
            $table->unsignedInteger('max_order_quantity')->nullable();
            $table->string('sku')->unique()->nullable();

            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->timestamp('discount_start')->nullable();
            $table->timestamp('discount_end')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->integer('view_count')->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('unit')->default('piece')->comment('piece, kg, gram, liter, etc');
            $table->boolean('is_virtual')->default(false)->comment('Is this a digital product?');
            $table->string('download_link')->nullable()->comment('For digital products');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null');
                  
            $table->foreign('main_image_id')
                  ->references('id')
                  ->on('attachments')
                  ->onDelete('set null');

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index(['is_active', 'is_featured', 'is_approved', 'category_id']);

        });

        Schema::create('product_attachments', function (Blueprint $table) {
            $table->uuid('product_id');
            $table->uuid('attachment_id');
            $table->string('type')->default('image');
            $table->integer('order')->default(0);
            
            $table->primary(['product_id', 'attachment_id']);
            
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
                  
            $table->foreign('attachment_id')
                  ->references('id')
                  ->on('attachments')
                  ->onDelete('cascade');
        });
        DB::statement("
            CREATE INDEX products_fulltext_idx ON products 
            USING GIN(to_tsvector('english', name || ' ' || description))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attachments');
        DB::statement('DROP INDEX IF EXISTS products_fulltext_idx');
        Schema::dropIfExists('products');
        Schema::enableForeignKeyConstraints();
    }
};