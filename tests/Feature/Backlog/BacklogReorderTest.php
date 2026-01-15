<?php

namespace Tests\Feature\Backlog;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacklogReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_reorder_backlog_items(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $itemA = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'position' => 1,
        ]);

        $itemB = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'position' => 2,
        ]);

        $positions = [
            (string) $itemA->id => 2,
            (string) $itemB->id => 1,
        ];

        $response = $this->actingAs($owner)->post(route('backlog.reorder', $project), [
            'positions' => $positions,
        ]);

        $response->assertRedirect(route('backlog.index', $project));

        $this->assertDatabaseHas('backlog_items', [
            'id' => $itemA->id,
            'position' => 2,
        ]);

        $this->assertDatabaseHas('backlog_items', [
            'id' => $itemB->id,
            'position' => 1,
        ]);
    }
}
