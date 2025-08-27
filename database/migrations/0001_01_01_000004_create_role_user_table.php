<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('role_id');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->primary(['user_id', 'role_id']);
            $table->index('expires_at');

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
                  
            $table->foreign('role_id')
                  ->references('id')->on('roles')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};

