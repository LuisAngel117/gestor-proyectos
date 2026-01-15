<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_member(): void
    {
        [$project, $owner, $teamMember] = $this->setupProjectWithTeamMember();

        $this->actingAs($owner)
            ->post(route('projects.members.store', $project), [
                'user_id' => $teamMember->id,
                'role' => 'member',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $teamMember->id,
            'role' => 'member',
        ]);
    }

    public function test_member_cannot_add_member(): void
    {
        [$project, $owner, $teamMember] = $this->setupProjectWithTeamMember();

        $member = User::factory()->create();
        $this->attachUserToTeam($project->team, $member, 'member');
        $this->attachUserToProject($project, $member, 'member');

        $this->actingAs($member)
            ->post(route('projects.members.store', $project), [
                'user_id' => $teamMember->id,
                'role' => 'member',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_change_role(): void
    {
        [$project, $owner, $teamMember] = $this->setupProjectWithTeamMember();

        $admin = User::factory()->create();
        $this->attachUserToTeam($project->team, $admin, 'admin');
        $this->attachUserToProject($project, $admin, 'admin');

        $this->attachUserToProject($project, $teamMember, 'member');

        $this->actingAs($admin)
            ->patch(route('projects.members.update', [$project, $teamMember]), [
                'role' => 'observer',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $teamMember->id,
            'role' => 'observer',
        ]);
    }

    public function test_cannot_remove_last_owner(): void
    {
        [$project, $owner] = $this->setupProjectWithTeamMember();

        $this->actingAs($owner)
            ->delete(route('projects.members.destroy', [$project, $owner]))
            ->assertRedirect(route('projects.show', $project))
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);
    }

    public function test_change_team_is_blocked_for_non_superadmin(): void
    {
        [$project, $owner] = $this->setupProjectWithTeamMember();

        $newTeam = Team::factory()->create(['owner_id' => $owner->id]);
        $this->attachUserToTeam($newTeam, $owner, 'owner');

        $this->actingAs($owner)
            ->put(route('projects.update', $project), [
                'team_id' => $newTeam->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'priority' => $project->priority,
            ])
            ->assertForbidden();
    }

    private function setupProjectWithTeamMember(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $this->attachUserToTeam($team, $owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $this->attachUserToProject($project, $owner, 'owner');

        $teamMember = User::factory()->create();
        $this->attachUserToTeam($team, $teamMember, 'member');

        return [$project, $owner, $teamMember];
    }

    private function attachUserToTeam(Team $team, User $user, string $role): void
    {
        $team->users()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    private function attachUserToProject(Project $project, User $user, string $role): void
    {
        $project->members()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
        ]);
    }
}
