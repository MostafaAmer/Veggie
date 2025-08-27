<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            
            $table->string('payment_id')->nullable()->index();
            $table->string('payment_method');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('EGP');
            
            $table->enum('type', [
                'payment', 'refund', 'partial_refund', 'chargeback', 'adjustment', 'withdrawal'
            ])->index();
            $table->enum('status', [
                'pending', 'completed', 'failed', 'cancelled', 'requires_action', 'on_hold'
            ])->default('pending')->index();

            
            $table->string('gateway')->nullable()->index();
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->json('gateway_response')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_at', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'type']);
            $table->index(['type', 'status']);
        });

        Schema::create('transaction_status_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('changed_by')->constrained('users')->cascadeOnDelete();
            
            $table->enum('status', [
                'pending', 'completed', 'failed', 'cancelled', 'requires_action', 'on_hold'
            ]);

            $table->string('action');
            $table->json('details');
            $table->timestamp('changed_at');
            $table->timestamps();
            
            $table->index(['transaction_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_status_logs');
        Schema::dropIfExists('transactions');
    }
};