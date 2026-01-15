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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'apellido')) {
                $table->string('apellido')->after('name');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['superadmin', 'user'])->default('user')->after('password');
            }

            if (!Schema::hasColumn('users', 'estado')) {
                $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo')->after('role');
            }

            if (!Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('estado');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('avatar_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }

            if (Schema::hasColumn('users', 'avatar_path')) {
                $table->dropColumn('avatar_path');
            }

            if (Schema::hasColumn('users', 'estado')) {
                $table->dropColumn('estado');
            }

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('users', 'apellido')) {
                $table->dropColumn('apellido');
            }
        });
    }
};
