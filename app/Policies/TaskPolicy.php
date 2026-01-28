<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Superadmin override.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperadmin() ? true : null;
    }

    public function viewAny(User $user, Project $project): bool
    {
        return $user->roleInProject($project->id) !== null
            || in_array($user->roleInTeam($project->team_id), ['owner', 'admin', 'observer'], true);
    }

    public function view(User $user, Task $task): bool
    {
        $project = $task->project;
        $teamRole = $user->roleInTeam($project->team_id);

        $projectRole = $user->roleInProject($project->id);

        if (in_array($projectRole, ['owner', 'admin'], true)) {
            return true;
        }

        if ($projectRole === 'member') {
            return $this->isAssigned($user, $task);
        }

        if ($projectRole === 'observer') {
            return $this->isAssigned($user, $task)
                || in_array($teamRole, ['owner', 'admin', 'observer'], true);
        }

        return in_array($teamRole, ['owner', 'admin', 'observer'], true);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->canManageTask($user, $project, ['owner', 'admin']);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->canManageTask($user, $task->project, ['owner', 'admin']);
    }

    public function updateStatus(User $user, Task $task): bool
    {
        if ($this->canManageTask($user, $task->project, ['owner', 'admin'])) {
            return true;
        }

        $projectRole = $user->roleInProject($task->project_id);
        return $projectRole === 'member' && $this->isAssigned($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->canManageTask($user, $task->project, ['owner', 'admin']);
    }

    public function manageDependencies(User $user, Task $task): bool
    {
        return $this->canManageTask($user, $task->project, ['owner', 'admin']);
    }

    public function toggleChecklist(User $user, Task $task): bool
    {
        if ($this->canManageTask($user, $task->project, ['owner', 'admin'])) {
            return true;
        }

        $projectRole = $user->roleInProject($task->project_id);
        return $projectRole === 'member' && $this->isAssigned($user, $task);
    }

    public function trackTime(User $user, Task $task): bool
    {
        if ($this->canManageTask($user, $task->project, ['owner', 'admin'])) {
            return true;
        }

        $projectRole = $user->roleInProject($task->project_id);
        return $projectRole === 'member' && $this->isAssigned($user, $task);
    }

    public function trackTimeManual(User $user, Task $task): bool
    {
        return $this->canManageTask($user, $task->project, ['owner', 'admin']);
    }

    private function canManageTask(User $user, Project $project, array $allowedRoles): bool
    {
        $projectRole = $user->roleInProject($project->id);
        if (in_array($projectRole, $allowedRoles, true)) {
            return true;
        }

        return in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }

    private function isAssigned(User $user, Task $task): bool
    {
        return $task->assignees()
            ->where('users.id', $user->id)
            ->exists();
    }
}
