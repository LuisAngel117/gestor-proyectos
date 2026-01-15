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
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE users SET role = 'user' WHERE role NOT IN ('superadmin', 'user')");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'user') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'user', 'observer', 'guest') NOT NULL DEFAULT 'user'");
    }
};
