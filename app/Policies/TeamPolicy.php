<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine if the user can view any teams.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de equipos
        return true;
    }

    /**
     * Determine if the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        // Superadmin puede ver todo
        if ($user->isSuperadmin()) {
            return true;
        }

        // Miembro del equipo puede verlo
        return $team->hasMember($user);
    }

    /**
     * Determine if the user can create teams.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear equipos
        return true;
    }

    /**
     * Determine if the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        // Superadmin puede actualizar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner o admin del equipo pueden actualizar
        return $team->userCan($user, 'update');
    }

    /**
     * Determine if the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        // Superadmin puede eliminar
        if ($user->isSuperadmin()) {
            return true;
        }

        // Solo el owner del equipo puede eliminarlo
        return $team->isOwner($user);
    }

    /**
     * Determine if the user can manage members of the team.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        // Superadmin puede gestionar miembros
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner o admin pueden gestionar miembros
        return $team->userCan($user, 'manageMembers');
    }

    /**
     * Determine if the user can create projects in the team.
     */
    public function createProject(User $user, Team $team): bool
    {
        // Superadmin puede crear proyectos
        if ($user->isSuperadmin()) {
            return true;
        }

        // Owner o admin pueden crear proyectos
        return $team->userCan($user, 'createProject');
    }

    /**
     * Determine if the user can view reports of the team.
     */
    public function viewReports(User $user, Team $team): bool
    {
        // Superadmin puede ver reportes
        if ($user->isSuperadmin()) {
            return true;
        }

        // Cualquier miembro puede ver reportes
        return $team->hasMember($user);
    }
}
