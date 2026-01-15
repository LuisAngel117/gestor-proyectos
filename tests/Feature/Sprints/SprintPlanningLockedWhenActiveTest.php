<?php

namespace Tests\Feature\Sprints;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintPlanningLockedWhenActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_assign_is_blocked_when_sprint_is_active(): void
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
            'created_by' => $owner->id,
            'status' => 'activo',
        ]);

        $item = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'sprint_id' => null,
            'status' => 'backlog',
        ]);

        $this->actingAs($owner)
            ->post(route('sprints.plan.assign', [$project, $sprint]), [
                'items' => [$item->id],
            ])
            ->assertForbidden();
    }
}
