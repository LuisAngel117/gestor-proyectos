<?php

namespace Tests\Feature\Middleware;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureTeamContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_when_user_has_multiple_teams_without_context(): void
    {
        $user = User::factory()->create();

        $teamA = Team::factory()->create();
        $teamA->addMember($user, 'member');

        $teamB = Team::factory()->create();
        $teamB->addMember($user, 'member');

        $response = $this->actingAs($user)->get(route('projects.index'));

        $response->assertRedirect(route('teams.index'));
    }

    public function test_auto_selects_team_when_user_has_single_team(): void
    {
        $user = User::factory()->create();

        $team = Team::factory()->create();
        $team->addMember($user, 'member');

        $response = $this->actingAs($user)->get(route('projects.index'));

        $response->assertOk();
    }

    public function test_blocks_access_when_user_not_in_project_team(): void
    {
        $user = User::factory()->create();

        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($user)->get(route('projects.show', $project));

        $response->assertForbidden();
    }

    public function test_superadmin_can_access_project_without_team_membership(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $team = Team::factory()->create();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $superadmin->id,
        ]);

        $response = $this->actingAs($superadmin)->get(route('projects.show', $project));

        $response->assertOk();
    }
}
