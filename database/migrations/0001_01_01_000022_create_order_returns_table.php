<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('user_id');
            
            $table->enum('status', [
                'pending', 
                'approved', 
                'rejected', 
                'refunded',
                'partially_refunded'
            ])->default('pending');
            
            $table->text('reason');
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->enum('refund_method', [
                'original_method', 
                'wallet_credit', 
                'bank_transfer'
            ])->nullable();
            
            $table->json('bank_details')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->uuid('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->uuid('refunded_by')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('refunded_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status', 'order_id', 'user_id']);
            $table->index('created_at');
            $table->index('refund_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};
