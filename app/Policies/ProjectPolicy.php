<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Superadmin override.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperadmin() ? true : null;
    }

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
        // Miembro del proyecto puede verlo
        if ($user->roleInProject($project->id) !== null) {
            return true;
        }

        // Admin u owner del team puede verlo (visión administrativa)
        return in_array($user->roleInTeam($project->team_id), ['owner', 'admin', 'observer']);
    }

    /**
     * Determine if the user can create projects.
     */
    public function create(User $user, ?Team $team = null): bool
    {
        if ($team) {
            return in_array($user->roleInTeam($team->id), ['owner', 'admin']);
        }

        return $user->teams()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine if the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // Owner o admin del proyecto pueden actualizar
        $role = $user->roleInProject($project->id);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // Solo el owner del proyecto puede eliminarlo
        return $user->roleInProject($project->id) === 'owner';
    }

    /**
     * Determine if the user can manage members of the project.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        $role = $user->roleInProject($project->id);
        return in_array($role, ['owner', 'admin'], true);
    }

    /**
     * Determine if the user can transfer project ownership.
     */
    public function transferOwnership(User $user, Project $project): bool
    {
        return $user->roleInProject($project->id) === 'owner';
    }

    /**
     * Block changing the project team (postponed).
     */
    public function changeTeam(User $user, Project $project): bool
    {
        return false;
    }
}
