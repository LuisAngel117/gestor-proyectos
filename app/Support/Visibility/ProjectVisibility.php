<?php

namespace App\Support\Visibility;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectVisibility
{
    public static function visibleProjectsFor(User $user): Builder
    {
        if ($user->isSuperadmin()) {
            return Project::query();
        }

        $adminTeamIds = $user->teams()
            ->wherePivotIn('role', ['owner', 'admin', 'observer'])
            ->pluck('teams.id');

        $memberProjectIds = $user->projects()
            ->pluck('projects.id');

        if ($adminTeamIds->isEmpty() && $memberProjectIds->isEmpty()) {
            return Project::query()->whereRaw('1 = 0');
        }

        return Project::query()->where(function (Builder $query) use ($adminTeamIds, $memberProjectIds) {
            if ($adminTeamIds->isNotEmpty()) {
                $query->whereIn('team_id', $adminTeamIds);
            }

            if ($memberProjectIds->isNotEmpty()) {
                $query->orWhereIn('id', $memberProjectIds);
            }
        });
    }

    public static function visibleProjectsForTeam(User $user, Team $team): HasMany
    {
        if ($user->isSuperadmin()) {
            return $team->projects();
        }

        $role = $user->roleInTeam($team->id);

        if (in_array($role, ['owner', 'admin', 'observer'], true)) {
            return $team->projects();
        }

        return $team->projects()
            ->whereHas('members', function (Builder $query) use ($user) {
                $query->where('users.id', $user->id);
            });
    }
}
