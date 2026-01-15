<?php

namespace Tests\Feature\Backlog;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Support\Context\TeamContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacklogReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_reorder_backlog_items(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $user->id,
        ]);

        $team->users()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);
        $project->members()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);

        $itemA = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'position' => 1,
            'created_by' => $user->id,
        ]);
        $itemB = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'position' => 2,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->withSession([TeamContext::SESSION_KEY => $team->id])
            ->post(route('backlog.reorder', $project), [
                'positions' => [
                    $itemA->id => 2,
                    $itemB->id => 1,
                ],
            ])
            ->assertRedirect(route('backlog.index', $project));

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
