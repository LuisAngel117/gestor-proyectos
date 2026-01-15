<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backlog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('priority')->default('media');
            $table->string('status')->default('backlog');
            $table->unsignedInteger('position')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'position']);
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'priority']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backlog_items');
    }
};
