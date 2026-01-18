<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedInteger('lock_version')->default(1)->after('updated_by');
            $table->unsignedInteger('edit_count')->default(0)->after('lock_version');
            $table->timestamp('edited_at')->nullable()->after('edit_count');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['edited_at', 'edit_count', 'lock_version']);
        });
    }
};
