<?php

namespace Tests\Feature\Sprints;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintPlanningReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_reorder_sprint_items(): void
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

        $itemA = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'sprint_id' => $sprint->id,
            'sprint_position' => 1,
        ]);

        $itemB = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'sprint_id' => $sprint->id,
            'sprint_position' => 2,
        ]);

        $response = $this->actingAs($owner)->post(route('sprints.plan.reorder', [$project, $sprint]), [
            'positions' => [
                (string) $itemA->id => 2,
                (string) $itemB->id => 1,
            ],
        ]);

        $response->assertRedirect(route('sprints.plan', [$project, $sprint]));

        $this->assertDatabaseHas('backlog_items', [
            'id' => $itemA->id,
            'sprint_position' => 2,
        ]);
    }
}
