<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $user->roleInProject($project->id) !== null
            || in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }

    public function view(User $user, Task $task): bool
    {
        $project = $task->project;

        return $user->roleInProject($project->id) !== null
            || in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->canManageTask($user, $project, ['owner', 'admin', 'member']);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->canManageTask($user, $task->project, ['owner', 'admin', 'member']);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->canManageTask($user, $task->project, ['owner', 'admin']);
    }

    public function manageDependencies(User $user, Task $task): bool
    {
        return $this->canManageTask($user, $task->project, ['owner', 'admin']);
    }

    public function trackTime(User $user, Task $task): bool
    {
        return $this->canManageTask($user, $task->project, ['owner', 'admin', 'member']);
    }

    private function canManageTask(User $user, Project $project, array $allowedRoles): bool
    {
        $projectRole = $user->roleInProject($project->id);
        if (in_array($projectRole, $allowedRoles, true)) {
            return true;
        }

        return in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }
}
