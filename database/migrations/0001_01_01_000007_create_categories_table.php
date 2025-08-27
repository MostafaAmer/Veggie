<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->string('color')->nullable()->comment('Hex color for category');
            $table->timestamps();
            $table->softDeletes();

            // $table->index(['name', 'slug', 'status', 'is_featured', 'parent_id']);
            
            // $table->foreignUuid('parent_id')
            //         ->nullable()
            //         ->constrained('categories')
            //         ->cascadeOnDelete();


            $table->foreignUuid('cover_image_id')
                  ->nullable()
                  ->constrained('attachments')
                  ->nullOnDelete();

            $table->foreignUuid('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
