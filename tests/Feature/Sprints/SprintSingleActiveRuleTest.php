<?php

namespace Tests\Feature\Sprints;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintSingleActiveRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_start_second_active_sprint_in_project(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $firstSprint = Sprint::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'status' => 'planificacion',
            'sequence' => 1,
        ]);

        $secondSprint = Sprint::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'status' => 'planificacion',
            'sequence' => 2,
        ]);

        $this->actingAs($owner)
            ->post(route('sprints.start', [$project, $firstSprint]))
            ->assertRedirect(route('sprints.show', [$project, $firstSprint]));

        $this->actingAs($owner)
            ->post(route('sprints.start', [$project, $secondSprint]))
            ->assertSessionHasErrors('status');

        $secondSprint->refresh();
        $this->assertSame('planificacion', $secondSprint->status);
        $this->assertNull($secondSprint->started_at);
    }
}
