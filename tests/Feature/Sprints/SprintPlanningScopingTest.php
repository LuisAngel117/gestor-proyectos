<?php

namespace Tests\Feature\Sprints;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintPlanningScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_assign_items_from_other_project(): void
    {
        $owner = User::factory()->create();

        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $projectA = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $projectA->addMember($owner, 'owner');

        $projectB = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $projectB->addMember($owner, 'owner');

        $sprint = Sprint::factory()->create([
            'project_id' => $projectA->id,
            'sequence' => 1,
        ]);

        $item = BacklogItem::factory()->create([
            'project_id' => $projectB->id,
            'sprint_id' => null,
            'status' => 'backlog',
        ]);

        $response = $this->actingAs($owner)->post(route('sprints.plan.assign', [$projectA, $sprint]), [
            'items' => [$item->id],
        ]);

        $response->assertSessionHasErrors('items');
    }
}
