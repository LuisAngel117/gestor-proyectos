<?php

namespace Tests\Feature\Middleware;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Support\Context\TeamContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureTeamContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_when_no_context_and_multiple_teams(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create(['owner_id' => $user->id]);
        $teamB = Team::factory()->create(['owner_id' => $user->id]);

        $teamA->users()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);
        $teamB->users()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($user)->get(route('projects.index'));

        $response->assertRedirect(route('teams.index'));
        $response->assertSessionHas('warning', 'Selecciona un equipo para continuar.');
        $this->assertSame(route('projects.index'), session('url.intended'));
    }

    public function test_auto_selects_team_when_user_has_single_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);

        $team->users()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($user)->get(route('projects.index'));

        $response->assertOk();
        $response->assertSessionHas(TeamContext::SESSION_KEY, $team->id);
    }

    public function test_forbids_invalid_team_context(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $otherTeam = Team::factory()->create();

        $team->users()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);

        $this->actingAs($user)
            ->withSession([TeamContext::SESSION_KEY => $otherTeam->id])
            ->get(route('projects.index'))
            ->assertForbidden();
    }

    public function test_forbids_project_outside_active_context(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create(['owner_id' => $user->id]);
        $teamB = Team::factory()->create();

        $teamA->users()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);

        $project = Project::factory()->create([
            'team_id' => $teamB->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->withSession([TeamContext::SESSION_KEY => $teamA->id])
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    public function test_superadmin_bypasses_team_membership(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $team = Team::factory()->create();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $superadmin->id,
        ]);

        $this->actingAs($superadmin)
            ->withSession([TeamContext::SESSION_KEY => $team->id])
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSessionHas(TeamContext::SESSION_KEY, $team->id);
    }
}
