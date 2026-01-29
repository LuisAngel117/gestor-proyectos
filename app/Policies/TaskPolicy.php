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

    public function create(User $user, Project $project, ?string $dueDate = null): bool
    {
        if (!$this->canManageTask($user, $project, ['owner', 'admin'])) {
            return false;
        }

        return $this->dueDateWithinProject($project, $dueDate);
    }

    public function update(User $user, Task $task, ?string $dueDate = null): bool
    {
        if (!$this->canManageTask($user, $task->project, ['owner', 'admin'])) {
            return false;
        }

        return $this->dueDateWithinProject($task->project, $dueDate);
    }

    public function updateStatus(User $user, Task $task): bool
    {
        return $this->canManageTask($user, $task->project, ['owner', 'admin']);
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
        if ($task->status === 'hecho') {
            return false;
        }

        if ($this->canManageTask($user, $task->project, ['owner', 'admin'])) {
            return true;
        }

        $projectRole = $user->roleInProject($task->project_id);
        if ($projectRole === 'member') {
            return $this->isAssigned($user, $task);
        }

        return $projectRole === null && $this->isAssigned($user, $task);
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

    private function dueDateWithinProject(Project $project, ?string $dueDate): bool
    {
        if (!$dueDate || !$project->due_date) {
            return true;
        }

        try {
            $taskDate = \Illuminate\Support\Carbon::parse($dueDate)->startOfDay();
            $projectDate = \Illuminate\Support\Carbon::parse($project->due_date)->startOfDay();
        } catch (\Throwable $e) {
            return false;
        }

        return $taskDate->lte($projectDate);
    }
}
