<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sprints', function (Blueprint $table) {
            if (!Schema::hasColumn('sprints', 'goal')) {
                $table->text('goal')->nullable()->after('name');
            }

            if (!Schema::hasColumn('sprints', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('sprints', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('started_at');
            }

            if (!Schema::hasColumn('sprints', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('project_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('sprints', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        DB::statement(
            "ALTER TABLE sprints MODIFY status ENUM(
                'planificado',
                'planificacion',
                'activo',
                'cerrado'
            ) NOT NULL DEFAULT 'planificacion'"
        );

        DB::table('sprints')
            ->where('status', 'planificado')
            ->update(['status' => 'planificacion']);

        DB::statement(
            "ALTER TABLE sprints MODIFY status ENUM(
                'planificacion',
                'activo',
                'cerrado'
            ) NOT NULL DEFAULT 'planificacion'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE sprints MODIFY status ENUM(
                'planificado',
                'planificacion',
                'activo',
                'cerrado'
            ) NOT NULL DEFAULT 'planificado'"
        );

        DB::table('sprints')
            ->where('status', 'planificacion')
            ->update(['status' => 'planificado']);

        DB::statement(
            "ALTER TABLE sprints MODIFY status ENUM(
                'planificado',
                'activo',
                'cerrado'
            ) NOT NULL DEFAULT 'planificado'"
        );

        Schema::table('sprints', function (Blueprint $table) {
            if (Schema::hasColumn('sprints', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            if (Schema::hasColumn('sprints', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('sprints', 'closed_at')) {
                $table->dropColumn('closed_at');
            }

            if (Schema::hasColumn('sprints', 'started_at')) {
                $table->dropColumn('started_at');
            }

            if (Schema::hasColumn('sprints', 'goal')) {
                $table->dropColumn('goal');
            }
        });
    }
};
