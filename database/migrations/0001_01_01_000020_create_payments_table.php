<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('parent_id')
                  ->nullable()
                  ->constrained('payments')
                  ->nullOnDelete()
                  ->comment('Parent payment for partial refunds');
            $table->enum('type', ['charge', 'refund'])
                  ->default('charge')
                  ->index();
            
            $table->decimal('amount', 12, 2);
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->enum('method', [
                'credit_card', 
                'wallet', 
                'cash_on_delivery',
                'bank_transfer'
            ])->index();
            $table->string('gateway', 32)
                  ->nullable()
                  ->index()
                  ->comment('Payment gateway driver identifier');
            $table->string('idempotency_key', 64)
                  ->nullable()
                  ->unique()
                  ->comment('Unique key for safe retries');
            
            $table->string('transaction_id')->nullable()->index();
            $table->string('currency', 3)->default('EGP');
            $table->enum('status', [
                'pending',
                'processing',
                'requires_action',
                'completed',
                'failed',
                'refunded',
                'partially_refunded',
            ])->default('pending')->index();
            
            $table->json('payment_details')->nullable();
            $table->json('gateway_response')->nullable();
            $table->string('error_code')->nullable();
            $table->json('error_data')->nullable()->comment('Detailed error payload');
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'method']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};