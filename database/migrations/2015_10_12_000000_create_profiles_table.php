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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('cargo')->nullable();
            $table->string('departamento')->nullable();
            $table->string('id_universitario')->nullable()->comment('Matrícula o ID universitario');
            $table->string('telefono', 20)->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();

            // Índice adicional para búsquedas por ID universitario
            $table->index('id_universitario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
