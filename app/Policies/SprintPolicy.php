<?php

namespace App\Policies;

use App\Models\Sprint;
use App\Models\User;

class SprintPolicy
{
    /**
     * Determine if the user can view any sprints.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de sprints
        return true;
    }

    /**
     * Determine if the user can view the sprint.
     */
    public function view(User $user, Sprint $sprint): bool
    {
        // Superadmin puede ver todo
        if ($user->isSuperadmin()) {
            return true;
        }

        // Miembro del proyecto puede ver el sprint
        return $user->roleInProject($sprint->project_id) !== null;
    }

    /**
     * Determine if the user can create sprints.
     */
    public function create(User $user, $project): bool
    {
        // Superadmin puede crear
        if ($user->isSuperadmin()) {
            return true;
        }

        // Solo owner y admin pueden crear sprints
        $role = $user->roleInProject($project->id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can update the sprint.
     */
    public function update(User $user, Sprint $sprint): bool
    {
        // Superadmin puede actualizar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Solo owner y admin pueden actualizar sprints
        $role = $user->roleInProject($sprint->project_id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can delete the sprint.
     */
    public function delete(User $user, Sprint $sprint): bool
    {
        // Superadmin puede eliminar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Solo owner puede eliminar sprints
        return $user->roleInProject($sprint->project_id) === 'owner';
    }

    /**
     * Determine if the user can start the sprint.
     */
    public function startSprint(User $user, Sprint $sprint): bool
    {
        // Superadmin puede iniciar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Solo owner y admin pueden iniciar sprints
        $role = $user->roleInProject($sprint->project_id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can close the sprint.
     */
    public function closeSprint(User $user, Sprint $sprint): bool
    {
        // Superadmin puede cerrar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Solo owner y admin pueden cerrar sprints
        $role = $user->roleInProject($sprint->project_id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can manage tasks in the sprint.
     */
    public function manageTasks(User $user, Sprint $sprint): bool
    {
        // Superadmin puede gestionar tareas
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner, admin y member pueden gestionar tareas
        $role = $user->roleInProject($sprint->project_id);
        return in_array($role, ['owner', 'admin', 'member']);
    }
}
