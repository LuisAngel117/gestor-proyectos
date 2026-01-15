<?php

namespace Tests\Feature\Visibility;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_only_its_teams(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create(['owner_id' => $user->id]);
        $teamB = Team::factory()->create();

        $teamA->users()->attach($user->id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('teams.index'))
            ->assertOk()
            ->assertSee($teamA->name)
            ->assertDontSee($teamB->name);
    }
}
