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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('apellido');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Campos adicionales para el proyecto
            $table->enum('role', ['superadmin', 'admin', 'user', 'observer', 'guest'])->default('user');
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->string('avatar_path')->nullable();
            $table->timestamp('last_login_at')->nullable();

            $table->rememberToken();
            $table->timestamps();

            // Índices para mejorar performance
            $table->index('role');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
