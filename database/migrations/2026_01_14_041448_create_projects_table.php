<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->enum('status', ['planificacion', 'en_progreso', 'en_espera', 'completado', 'cancelado'])->default('planificacion');
            $table->enum('priority', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Índices para mejorar performance
            $table->index('team_id');
            $table->index('status');
            $table->index('priority');
            $table->index('created_by');
            $table->index(['team_id', 'status'], 'team_status_idx');

            // Slug único por equipo
            $table->unique(['team_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
