<?php

namespace Tests\Feature\Backlog;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Support\Context\TeamContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacklogCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_update_and_archive_backlog_item(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $user->id]);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $user->id,
        ]);

        $team->users()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);
        $project->members()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);

        $this->actingAs($user)
            ->withSession([TeamContext::SESSION_KEY => $team->id])
            ->post(route('backlog.store', $project), [
                'name' => 'Ítem inicial',
                'description' => 'Descripción inicial',
                'priority' => 'alta',
            ])
            ->assertRedirect(route('backlog.index', $project));

        $item = BacklogItem::firstOrFail();

        $this->actingAs($user)
            ->withSession([TeamContext::SESSION_KEY => $team->id])
            ->put(route('backlog.update', [$project, $item]), [
                'name' => 'Ítem actualizado',
                'description' => 'Nueva descripción',
                'priority' => 'urgente',
                'status' => 'refinado',
            ])
            ->assertRedirect(route('backlog.index', $project));

        $this->assertDatabaseHas('backlog_items', [
            'id' => $item->id,
            'name' => 'Ítem actualizado',
            'status' => 'refinado',
        ]);

        $this->actingAs($user)
            ->withSession([TeamContext::SESSION_KEY => $team->id])
            ->delete(route('backlog.destroy', [$project, $item]))
            ->assertRedirect(route('backlog.index', $project));

        $this->assertSoftDeleted('backlog_items', [
            'id' => $item->id,
        ]);
    }
}
