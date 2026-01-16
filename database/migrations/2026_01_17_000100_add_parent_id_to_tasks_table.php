<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('backlog_item_id')
                ->constrained('tasks')
                ->nullOnDelete();

            $table->index('parent_id');
            $table->index(['project_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
