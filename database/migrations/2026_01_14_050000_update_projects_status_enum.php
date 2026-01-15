<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE projects MODIFY status ENUM(
                'planificacion',
                'en_progreso',
                'en_espera',
                'completado',
                'cancelado',
                'archivado'
            ) NOT NULL DEFAULT 'planificacion'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE projects MODIFY status ENUM(
                'planificacion',
                'en_progreso',
                'en_espera',
                'completado',
                'cancelado'
            ) NOT NULL DEFAULT 'planificacion'"
        );
    }
};
