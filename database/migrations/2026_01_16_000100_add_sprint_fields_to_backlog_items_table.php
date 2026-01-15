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
        Schema::table('backlog_items', function (Blueprint $table) {
            $table->foreignId('sprint_id')
                ->nullable()
                ->constrained('sprints')
                ->nullOnDelete()
                ->after('project_id');
            $table->unsignedInteger('sprint_position')->nullable()->after('position');

            $table->index(['sprint_id', 'sprint_position']);
            $table->index(['project_id', 'sprint_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backlog_items', function (Blueprint $table) {
            $table->dropIndex(['sprint_id', 'sprint_position']);
            $table->dropIndex(['project_id', 'sprint_id']);
            $table->dropConstrainedForeignId('sprint_id');
            $table->dropColumn('sprint_position');
        });
    }
};
