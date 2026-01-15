<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;

class SprintPolicy
{
    /**
     * Determine whether the user can view the sprint.
     */
    public function view(User $user, Sprint $sprint): bool
    {
        $project = $sprint->project;

        return $user->roleInProject($project->id) !== null
            || in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }

    /**
     * Determine whether the user can plan a sprint backlog.
     */
    public function plan(User $user, Sprint $sprint): bool
    {
        return $this->canManageSprint($user, $sprint) && $sprint->isPlanning();
    }

    /**
     * Determine whether the user can reorder sprint backlog items.
     */
    public function reorderBacklog(User $user, Sprint $sprint): bool
    {
        return $this->canManageSprint($user, $sprint) && $sprint->isPlanning();
    }

    /**
     * Determine whether the user can start a sprint.
     */
    public function startSprint(User $user, Sprint $sprint): bool
    {
        return $this->canManageSprint($user, $sprint);
    }

    /**
     * Determine whether the user can close a sprint.
     */
    public function closeSprint(User $user, Sprint $sprint): bool
    {
        return $this->canManageSprint($user, $sprint);
    }

    private function canManageSprint(User $user, Sprint $sprint): bool
    {
        $project = $sprint->project;
        $projectRole = $user->roleInProject($project->id);

        if (in_array($projectRole, ['owner', 'admin'], true)) {
            return true;
        }

        return in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }
}
