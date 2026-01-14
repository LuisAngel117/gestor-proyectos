<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * IMPORTANTE: Esta migración requiere que la tabla 'projects' exista.
     * Ejecutar DESPUÉS de la migración create_projects_table (M-06).
     */
    public function up(): void
    {
        // Verificar que la tabla projects existe antes de crear la FK
        if (!Schema::hasTable('projects')) {
            throw new \Exception('La tabla "projects" no existe. Ejecuta primero la migración create_projects_table (M-06).');
        }

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'member', 'observer'])->default('member');
            $table->json('permissions')->nullable()->comment('Permisos específicos por usuario en formato JSON');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            // Evitar duplicados: un usuario solo puede estar una vez en un proyecto
            $table->unique(['project_id', 'user_id']);

            // Índices para mejorar performance
            $table->index('project_id');
            $table->index('user_id');
            $table->index('role');
            $table->index(['project_id', 'user_id', 'role'], 'project_user_composite_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
