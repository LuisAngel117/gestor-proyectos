<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    public function view(User $user, Comment $comment): bool
    {
        $task = $comment->task;
        if (!$task) {
            return false;
        }

        $project = $task->project;
        $projectRole = $user->roleInProject($project->id);

        if (in_array($projectRole, ['owner', 'admin'], true)) {
            return true;
        }

        if ($projectRole === 'member') {
            return $task->assignees()->where('users.id', $user->id)->exists();
        }

        if ($projectRole === 'observer') {
            return $task->assignees()->where('users.id', $user->id)->exists()
                || in_array($user->roleInTeam($project->team_id), ['owner', 'admin', 'observer'], true);
        }

        return in_array($user->roleInTeam($project->team_id), ['owner', 'admin', 'observer'], true);
    }

    public function create(User $user, Task $task): bool
    {
        $project = $task->project;
        $projectRole = $user->roleInProject($project->id);

        if (in_array($projectRole, ['owner', 'admin'], true)) {
            return true;
        }

        if ($projectRole === 'member') {
            return $task->assignees()->where('users.id', $user->id)->exists();
        }

        if ($projectRole === 'observer') {
            return false;
        }

        return in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $this->canModerateOrOwn($user, $comment);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $this->canModerateOrOwn($user, $comment);
    }

    private function canModerateOrOwn(User $user, Comment $comment): bool
    {
        $task = $comment->task;
        if (!$task) {
            return false;
        }

        $project = $task->project;
        $projectRole = $user->roleInProject($project->id);

        if (in_array($projectRole, ['owner', 'admin'], true)) {
            return true;
        }

        if (in_array($user->roleInTeam($project->team_id), ['owner', 'admin'], true)) {
            return true;
        }

        if ($projectRole !== 'member') {
            return false;
        }

        $isAssigned = $task->assignees()->where('users.id', $user->id)->exists();
        return $isAssigned && (int) $comment->created_by === (int) $user->id;
    }
}
