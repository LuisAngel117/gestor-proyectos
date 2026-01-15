<?php

namespace Tests\Feature\Backlog;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Support\Context\TeamContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacklogAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_create_or_update_backlog_items(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $user->id,
        ]);

        $team->users()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);
        $project->members()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);

        $item = BacklogItem::factory()->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->withSession([TeamContext::SESSION_KEY => $team->id])
            ->post(route('backlog.store', $project), [
                'name' => 'Nuevo ítem',
                'priority' => 'media',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->withSession([TeamContext::SESSION_KEY => $team->id])
            ->put(route('backlog.update', [$project, $item]), [
                'name' => 'Cambio',
                'priority' => 'baja',
                'status' => 'backlog',
            ])
            ->assertForbidden();
    }
}
