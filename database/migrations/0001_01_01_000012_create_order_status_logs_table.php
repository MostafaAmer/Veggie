<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            
            $table->uuid('order_id');
            $table->uuid('changed_by')->nullable();
            
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'refunded'
            ])->index();
            
            $table->text('notes')->nullable(); 
            $table->json('extra_data')->nullable(); 
            $table->timestamp('changed_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->timestamps();
            
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
                  
            $table->foreign('changed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            $table->index(['order_id', 'status']);
            $table->index('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
    }
};