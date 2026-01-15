<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectOwnershipTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_transfer_ownership(): void
    {
        [$project, $owner, $newOwner] = $this->setupProjectWithNewOwner();

        $this->actingAs($owner)
            ->post(route('projects.ownership.transfer', $project), [
                'user_id' => $newOwner->id,
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $newOwner->id,
            'role' => 'owner',
        ]);
    }

    public function test_admin_cannot_transfer_ownership(): void
    {
        [$project, $owner, $newOwner] = $this->setupProjectWithNewOwner();

        $admin = User::factory()->create();
        $this->attachUserToTeam($project->team, $admin, 'admin');
        $this->attachUserToProject($project, $admin, 'admin');

        $this->actingAs($admin)
            ->post(route('projects.ownership.transfer', $project), [
                'user_id' => $newOwner->id,
            ])
            ->assertForbidden();
    }

    public function test_transfer_requires_team_membership(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $this->attachUserToTeam($team, $owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $this->attachUserToProject($project, $owner, 'owner');

        $outsider = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('projects.ownership.transfer', $project), [
                'user_id' => $outsider->id,
            ])
            ->assertSessionHasErrors('user_id');
    }

    private function setupProjectWithNewOwner(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $this->attachUserToTeam($team, $owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $this->attachUserToProject($project, $owner, 'owner');

        $newOwner = User::factory()->create();
        $this->attachUserToTeam($team, $newOwner, 'member');
        $this->attachUserToProject($project, $newOwner, 'member');

        return [$project, $owner, $newOwner];
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
