<?php

namespace Tests\Feature\Backlog;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacklogCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_backlog_item(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $response = $this->actingAs($owner)->post(route('backlog.store', $project), [
            'name' => 'Nueva historia',
            'description' => 'Descripción del backlog',
            'priority' => 'media',
        ]);

        $response->assertRedirect(route('backlog.index', $project));

        $this->assertDatabaseHas('backlog_items', [
            'project_id' => $project->id,
            'name' => 'Nueva historia',
            'priority' => 'media',
        ]);
    }
}
