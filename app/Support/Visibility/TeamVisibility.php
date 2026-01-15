<?php

namespace App\Support\Visibility;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TeamVisibility
{
    public static function visibleTeamsFor(User $user): Builder
    {
        if ($user->isSuperadmin()) {
            return Team::query();
        }

        return $user->teams()->getQuery();
    }
}
