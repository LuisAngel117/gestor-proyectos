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
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $newOwner = User::factory()->create();
        $team->addMember($newOwner, 'member');
        $project->addMember($newOwner, 'member');

        $response = $this->actingAs($owner)
            ->post(route('projects.ownership.transfer', $project), [
                'user_id' => $newOwner->id,
            ]);

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $newOwner->id,
            'role' => 'owner',
        ]);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_cannot_transfer_ownership(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $admin = User::factory()->create();
        $team->addMember($admin, 'admin');
        $project->addMember($admin, 'admin');

        $response = $this->actingAs($admin)
            ->post(route('projects.ownership.transfer', $project), [
                'user_id' => $admin->id,
            ]);

        $response->assertForbidden();
    }
}
