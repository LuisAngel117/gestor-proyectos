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
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('name');
            $table->unsignedInteger('sequence');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['planificado', 'activo', 'cerrado'])->default('planificado');
            $table->timestamps();

            $table->unique(['project_id', 'sequence']);
            $table->index('project_id');
            $table->index(['project_id', 'start_date']);
            $table->index(['project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sprints');
    }
};
