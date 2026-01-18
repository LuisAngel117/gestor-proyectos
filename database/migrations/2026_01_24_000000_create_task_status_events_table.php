<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['project_id', 'changed_at']);
            $table->index(['task_id', 'changed_at']);
            $table->index(['to_status', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_status_events');
    }
};
