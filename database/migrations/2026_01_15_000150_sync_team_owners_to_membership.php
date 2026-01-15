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
        $teams = DB::table('teams')->select('id', 'owner_id')->get();

        foreach ($teams as $team) {
            if (!$team->owner_id) {
                continue;
            }

            $exists = DB::table('team_user')
                ->where('team_id', $team->id)
                ->where('user_id', $team->owner_id)
                ->exists();

            if (!$exists) {
                DB::table('team_user')->insert([
                    'team_id' => $team->id,
                    'user_id' => $team->owner_id,
                    'role' => 'owner',
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: evitar eliminar registros existentes de owner en pivots.
    }
};
