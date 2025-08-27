<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedTinyInteger('level')->default(0)->check('level >= 0 AND level <= 255');
            $table->boolean('is_staff')->default(false)->comment('Is this role for staff members?');
            $table->boolean('is_customer')->default(true)->comment('Is this role for customers?');
            $table->softDeletes(); 
            $table->timestamps();

            $table->index(['slug']);
            $table->index(['is_default', 'is_staff', 'is_customer']);
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
