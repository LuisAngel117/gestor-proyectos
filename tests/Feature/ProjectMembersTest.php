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

    public function test_owner_can_add_member_to_project(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $member = User::factory()->create();
        $team->addMember($member, 'member');

        $response = $this->actingAs($owner)
            ->post(route('projects.members.store', $project), [
                'user_id' => $member->id,
                'role' => 'member',
            ]);

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);
    }

    public function test_member_cannot_add_project_members(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $member = User::factory()->create();
        $team->addMember($member, 'member');
        $project->addMember($member, 'member');

        $target = User::factory()->create();
        $team->addMember($target, 'member');

        $response = $this->actingAs($member)
            ->post(route('projects.members.store', $project), [
                'user_id' => $target->id,
                'role' => 'member',
            ]);

        $response->assertForbidden();
    }

    public function test_cannot_remove_last_owner_from_project(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $response = $this->actingAs($owner)
            ->delete(route('projects.members.destroy', [$project, $owner]));

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHasErrors('user_id');

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);
    }
}
