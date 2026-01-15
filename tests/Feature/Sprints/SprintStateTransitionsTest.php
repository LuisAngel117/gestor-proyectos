<?php

namespace Tests\Feature\Sprints;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintStateTransitionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_start_and_close_sprint(): void
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
            'status' => 'planificacion',
        ]);

        $startResponse = $this->actingAs($owner)
            ->post(route('sprints.start', [$project, $sprint]));

        $startResponse->assertRedirect(route('sprints.show', [$project, $sprint]));

        $sprint->refresh();
        $this->assertSame('activo', $sprint->status);
        $this->assertNotNull($sprint->started_at);

        $closeResponse = $this->actingAs($owner)
            ->post(route('sprints.close', [$project, $sprint]));

        $closeResponse->assertRedirect(route('sprints.show', [$project, $sprint]));

        $sprint->refresh();
        $this->assertSame('cerrado', $sprint->status);
        $this->assertNotNull($sprint->closed_at);
    }
}
