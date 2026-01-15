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
        if (Schema::hasColumn('project_user', 'permissions')) {
            Schema::table('project_user', function (Blueprint $table) {
                $table->dropColumn('permissions');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('project_user', 'permissions')) {
            Schema::table('project_user', function (Blueprint $table) {
                $table->json('permissions')->nullable()->comment('Permisos específicos por usuario en formato JSON');
            });
        }
    }
};
