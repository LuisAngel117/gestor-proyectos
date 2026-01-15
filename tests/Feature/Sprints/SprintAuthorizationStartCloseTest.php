<?php

namespace Tests\Feature\Sprints;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintAuthorizationStartCloseTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_start_or_close_sprint(): void
    {
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->addMember($member, 'member');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $member->id,
        ]);
        $project->addMember($member, 'member');

        $sprint = Sprint::factory()->create([
            'project_id' => $project->id,
            'created_by' => $member->id,
            'status' => 'planificacion',
        ]);

        $this->actingAs($member)
            ->post(route('sprints.start', [$project, $sprint]))
            ->assertForbidden();

        $sprint->update(['status' => 'activo']);

        $this->actingAs($member)
            ->post(route('sprints.close', [$project, $sprint]))
            ->assertForbidden();
    }
}
