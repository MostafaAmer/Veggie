<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->uuid('uploaded_by')->nullable();
            $table->string('disk')->default('public');
            $table->string('type')->default('image'); 
            $table->json('custom_properties')->nullable();
            $table->string('hash')->nullable()->index()->comment('File hash for deduplication');
            $table->unsignedInteger('width')->nullable()->comment('For images/videos');
            $table->unsignedInteger('height')->nullable()->comment('For images/videos');
            $table->unsignedInteger('duration')->nullable()->comment('For audio/video');
            $table->string('alt_text')->nullable()->comment('For accessibility');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'uploaded_by', 'created_at']);

            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
