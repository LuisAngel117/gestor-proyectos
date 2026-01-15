<?php

namespace Tests\Feature\Sprints;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintPlanningAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_assign_items(): void
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

        $sprint = Sprint::factory()->create([
            'project_id' => $project->id,
            'sequence' => 1,
        ]);

        $item = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'sprint_id' => null,
            'status' => 'backlog',
        ]);

        $response = $this->actingAs($member)->post(route('sprints.plan.assign', [$project, $sprint]), [
            'items' => [$item->id],
        ]);

        $response->assertForbidden();
    }
}
