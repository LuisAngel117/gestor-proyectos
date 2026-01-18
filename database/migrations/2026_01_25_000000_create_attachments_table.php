<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('extension', 20);
            $table->string('mime_type', 255);
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('disk')->default('local');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['task_id', 'deleted_at']);
            $table->index(['project_id', 'deleted_at']);
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
