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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')
                    ->default(false)
                    ->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'profile_completed_at')) {
                $table->timestamp('profile_completed_at')
                    ->nullable()
                    ->after('must_change_password');
            }
        });

        // Mark existing users as completed to avoid forcing legacy accounts.
        DB::table('users')
            ->whereNull('profile_completed_at')
            ->update(['profile_completed_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'profile_completed_at')) {
                $table->dropColumn('profile_completed_at');
            }
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
        });
    }
};
