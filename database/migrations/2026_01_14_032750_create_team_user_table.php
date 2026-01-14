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
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'member', 'observer'])->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            // Evitar duplicados: un usuario solo puede estar una vez en un equipo
            $table->unique(['team_id', 'user_id']);

            // Índices para mejorar performance en consultas
            $table->index('team_id');
            $table->index('user_id');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};
