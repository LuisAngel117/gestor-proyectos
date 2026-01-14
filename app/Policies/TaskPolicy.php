<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine if the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de tareas
        return true;
    }

    /**
     * Determine if the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        // Superadmin puede ver todo
        if ($user->isSuperadmin()) {
            return true;
        }

        // Miembro del proyecto puede ver la tarea
        return $user->roleInProject($task->project_id) !== null;
    }

    /**
     * Determine if the user can create tasks.
     */
    public function create(User $user, $project): bool
    {
        // Superadmin puede crear
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner, admin y member pueden crear tareas
        $role = $user->roleInProject($project->id);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        // Superadmin puede actualizar
        if ($user->isSuperadmin()) {
            return true;
        }

        $role = $user->roleInProject($task->project_id);

        // Owner y admin pueden actualizar cualquier tarea
        if (in_array($role, ['owner', 'admin'])) {
            return true;
        }

        // Member solo puede actualizar sus propias tareas asignadas
        if ($role === 'member') {
            return $task->assignees()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine if the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        // Superadmin puede eliminar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Solo owner y admin pueden eliminar tareas
        $role = $user->roleInProject($task->project_id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can comment on the task.
     */
    public function comment(User $user, Task $task): bool
    {
        // Superadmin puede comentar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner, admin y member pueden comentar
        $role = $user->roleInProject($task->project_id);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if the user can register time on the task.
     */
    public function registerTime(User $user, Task $task): bool
    {
        // Superadmin puede registrar tiempo
        if ($user->isSuperadmin()) {
            return true;
        }

        $role = $user->roleInProject($task->project_id);

        // Owner y admin pueden registrar tiempo en cualquier tarea
        if (in_array($role, ['owner', 'admin'])) {
            return true;
        }

        // Member solo puede registrar tiempo en sus tareas asignadas
        if ($role === 'member') {
            return $task->assignees()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine if the user can manage dependencies of the task.
     */
    public function manageDependencies(User $user, Task $task): bool
    {
        // Superadmin puede gestionar dependencias
        if ($user->isSuperadmin()) {
            return true;
        }

        // Solo owner y admin pueden gestionar dependencias
        $role = $user->roleInProject($task->project_id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can attach files to the task.
     */
    public function attachFiles(User $user, Task $task): bool
    {
        // Superadmin puede adjuntar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner, admin y member pueden adjuntar archivos
        $role = $user->roleInProject($task->project_id);
        return in_array($role, ['owner', 'admin', 'member']);
    }
}
