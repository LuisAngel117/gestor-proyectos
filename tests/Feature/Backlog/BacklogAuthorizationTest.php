<?php

namespace Tests\Feature\Backlog;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacklogAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_create_backlog_item(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->addMember($owner, 'owner');

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->addMember($owner, 'owner');

        $member = User::factory()->create();
        $team->addMember($member, 'member');
        $project->addMember($member, 'member');

        $response = $this->actingAs($member)->post(route('backlog.store', $project), [
            'name' => 'Item no autorizado',
            'priority' => 'media',
        ]);

        $response->assertForbidden();
    }
}
