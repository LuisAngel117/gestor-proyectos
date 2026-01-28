<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Superadmin override.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperadmin() ? true : null;
    }

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
        // Miembro del equipo puede verlo
        return $team->hasMember($user);
    }

    /**
     * Determine if the user can create teams.
     */
    public function create(User $user): bool
    {
        // Solo admins del sistema pueden crear equipos
        return in_array($user->role, ['admin', 'superadmin'], true);
    }

    /**
     * Determine if the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        // Owner o admin del equipo pueden actualizar
        return $team->userCan($user, 'update');
    }

    /**
     * Determine if the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        // Solo el owner del equipo puede eliminarlo
        return $team->isOwner($user);
    }

    /**
     * Determine if the user can manage members of the team.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        // Owner o admin pueden gestionar miembros
        return $team->userCan($user, 'manageMembers');
    }
}
