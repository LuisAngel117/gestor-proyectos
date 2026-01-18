<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('task_time_entries')
            ->whereNull('duration_seconds')
            ->update(['duration_seconds' => 0]);
        DB::statement('ALTER TABLE task_time_entries MODIFY duration_seconds INT UNSIGNED NOT NULL DEFAULT 0');

        Schema::table('task_time_entries', function (Blueprint $table) {
            $table->string('source', 20)->default('timer')->after('duration_seconds');
            $table->text('note')->nullable()->after('source');
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete()->after('note');
            $table->index(['user_id', 'started_at', 'stopped_at'], 'task_time_entries_user_time_index');
        });
    }

    public function down(): void
    {
        Schema::table('task_time_entries', function (Blueprint $table) {
            $table->dropIndex('task_time_entries_user_time_index');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['source', 'note']);
        });

        DB::statement('ALTER TABLE task_time_entries MODIFY duration_seconds INT UNSIGNED NULL');
    }
};
