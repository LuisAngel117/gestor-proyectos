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

    public function test_member_only_sees_assigned_projects(): void
    {
        $member = User::factory()->create();
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($member, 'member');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($member)
            ->get(route('projects.index', ['team' => $team->id]));

        $response->assertOk();
        $response->assertDontSee($project->name);
    }

    public function test_member_cannot_access_unassigned_project(): void
    {
        $member = User::factory()->create();
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($member, 'member');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($member)
            ->get(route('projects.show', $project));

        $response->assertForbidden();
    }
}
