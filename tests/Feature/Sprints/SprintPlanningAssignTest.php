<?php

namespace Tests\Feature\Sprints;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintPlanningAssignTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_assign_items_to_sprint(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $sprint = Sprint::factory()->create([
            'project_id' => $project->id,
            'sequence' => 1,
        ]);

        $item = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'sprint_id' => null,
            'status' => 'backlog',
        ]);

        $response = $this->actingAs($owner)->post(route('sprints.plan.assign', [$project, $sprint]), [
            'items' => [$item->id],
        ]);

        $response->assertRedirect(route('sprints.plan', [$project, $sprint]));

        $this->assertDatabaseHas('backlog_items', [
            'id' => $item->id,
            'sprint_id' => $sprint->id,
            'sprint_position' => 1,
        ]);
    }
}
