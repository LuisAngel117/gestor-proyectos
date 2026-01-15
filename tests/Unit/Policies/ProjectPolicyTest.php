<?php

namespace Tests\Unit\Policies;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_manage_members_allows_owner_and_admin(): void
    {
        [$project, $owner, $admin] = $this->setupProjectWithAdmin();
        $policy = new ProjectPolicy();

        $this->assertTrue($policy->manageMembers($owner, $project));
        $this->assertTrue($policy->manageMembers($admin, $project));
    }

    public function test_manage_members_denies_member(): void
    {
        [$project, $member] = $this->setupProjectWithMember();
        $policy = new ProjectPolicy();

        $this->assertFalse($policy->manageMembers($member, $project));
    }

    public function test_transfer_ownership_only_owner(): void
    {
        [$project, $owner, $admin] = $this->setupProjectWithAdmin();
        $policy = new ProjectPolicy();

        $this->assertTrue($policy->transferOwnership($owner, $project));
        $this->assertFalse($policy->transferOwnership($admin, $project));
    }

    public function test_change_team_is_blocked(): void
    {
        [$project, $owner] = $this->setupProjectWithOwner();
        $policy = new ProjectPolicy();

        $this->assertFalse($policy->changeTeam($owner, $project));
    }

    private function setupProjectWithOwner(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->users()->attach($owner->id, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
        ]);
        $project->members()->attach($owner->id, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        return [$project, $owner];
    }

    private function setupProjectWithAdmin(): array
    {
        [$project, $owner] = $this->setupProjectWithOwner();

        $admin = User::factory()->create();
        $project->team->users()->attach($admin->id, [
            'role' => 'admin',
            'joined_at' => now(),
        ]);
        $project->members()->attach($admin->id, [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        return [$project, $owner, $admin];
    }

    private function setupProjectWithMember(): array
    {
        [$project] = $this->setupProjectWithOwner();

        $member = User::factory()->create();
        $project->team->users()->attach($member->id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);
        $project->members()->attach($member->id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        return [$project, $member];
    }
}
