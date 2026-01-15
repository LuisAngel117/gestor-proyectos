<?php

namespace Tests\Feature\Visibility;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_index_shows_only_memberships(): void
    {
        $member = User::factory()->create();

        $owner = User::factory()->create();
        $teamVisible = Team::factory()->create(['owner_id' => $owner->id]);
        $teamVisible->addMember($member, 'member');

        $teamHidden = Team::factory()->create();

        $response = $this->actingAs($member)->get(route('teams.index'));

        $response->assertOk();
        $response->assertSee($teamVisible->name);
        $response->assertDontSee($teamHidden->name);
    }
}
