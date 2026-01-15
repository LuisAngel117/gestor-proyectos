<?php

namespace Tests\Feature\Visibility;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_only_assigned_projects_in_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $team->users()->attach($user->id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $projectVisible = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'name' => 'Proyecto Visible',
        ]);
        $projectHidden = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'name' => 'Proyecto Oculto',
        ]);

        $projectVisible->members()->attach($user->id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('teams.show', $team))
            ->assertOk()
            ->assertSee('Proyecto Visible')
            ->assertDontSee('Proyecto Oculto');
    }

    public function test_member_cannot_access_unassigned_project(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $team->users()->attach($user->id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }
}
