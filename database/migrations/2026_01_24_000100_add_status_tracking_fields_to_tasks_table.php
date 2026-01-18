<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('status_changed_at')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('status_changed_at');
            $table->index(['project_id', 'completed_at'], 'tasks_project_completed_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_project_completed_at_index');
            $table->dropColumn('completed_at');
            $table->dropColumn('status_changed_at');
        });
    }
};
