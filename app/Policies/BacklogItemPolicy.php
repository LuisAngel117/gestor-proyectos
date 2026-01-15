<?php

namespace App\Policies;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\User;

class BacklogItemPolicy
{
    /**
     * Superadmin override.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperadmin() ? true : null;
    }

    /**
     * Determine whether the user can view any backlog items for a project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $user->roleInProject($project->id) !== null
            || in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }

    /**
     * Determine whether the user can view the backlog item.
     */
    public function view(User $user, BacklogItem $backlogItem): bool
    {
        $project = $backlogItem->project;

        return $user->roleInProject($project->id) !== null
            || in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }

    /**
     * Determine whether the user can create backlog items.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->canManageBacklog($user, $project);
    }

    /**
     * Determine whether the user can update the backlog item.
     */
    public function update(User $user, BacklogItem $backlogItem): bool
    {
        return $this->canManageBacklog($user, $backlogItem->project);
    }

    /**
     * Determine whether the user can delete the backlog item.
     */
    public function delete(User $user, BacklogItem $backlogItem): bool
    {
        return $this->canManageBacklog($user, $backlogItem->project);
    }

    /**
     * Determine whether the user can reorder backlog items.
     */
    public function reorder(User $user, Project $project): bool
    {
        return $this->canManageBacklog($user, $project);
    }

    private function canManageBacklog(User $user, Project $project): bool
    {
        $projectRole = $user->roleInProject($project->id);
        if (in_array($projectRole, ['owner', 'admin'], true)) {
            return true;
        }

        return in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }
}
