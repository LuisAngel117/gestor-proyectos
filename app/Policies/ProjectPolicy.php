<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine if the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de proyectos
        return true;
    }

    /**
     * Determine if the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        // Superadmin puede ver todo
        if ($user->isSuperadmin()) {
            return true;
        }

        // Miembro del proyecto puede verlo
        return $user->roleInProject($project->id) !== null;
    }

    /**
     * Determine if the user can create projects.
     */
    public function create(User $user): bool
    {
        // Superadmin puede crear
        if ($user->isSuperadmin()) {
            return true;
        }

        // Usuario debe ser owner o admin de al menos un equipo
        return $user->teams()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine if the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // Superadmin puede actualizar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner o admin del proyecto pueden actualizar
        $role = $user->roleInProject($project->id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // Superadmin puede eliminar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Solo el owner del proyecto puede eliminarlo
        return $user->roleInProject($project->id) === 'owner';
    }

    /**
     * Determine if the user can manage members of the project.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        // Superadmin puede gestionar miembros
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner o admin pueden gestionar miembros
        $role = $user->roleInProject($project->id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can manage sprints in the project.
     */
    public function manageSprints(User $user, Project $project): bool
    {
        // Superadmin puede gestionar sprints
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner o admin pueden gestionar sprints
        $role = $user->roleInProject($project->id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can export data from the project.
     */
    public function export(User $user, Project $project): bool
    {
        // Superadmin puede exportar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Cualquier miembro puede exportar (excepto guests si existen)
        return $user->roleInProject($project->id) !== null;
    }

    /**
     * Determine if the user can attach files to the project.
     */
    public function attachFiles(User $user, Project $project): bool
    {
        // Superadmin puede adjuntar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner, admin y member pueden adjuntar archivos
        $role = $user->roleInProject($project->id);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if the user can comment on the project.
     */
    public function comment(User $user, Project $project): bool
    {
        // Superadmin puede comentar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner, admin y member pueden comentar
        $role = $user->roleInProject($project->id);
        return in_array($role, ['owner', 'admin', 'member']);
    }
}
