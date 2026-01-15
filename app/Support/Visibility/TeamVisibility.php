<?php

namespace App\Support\Visibility;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeamVisibility
{
    public static function visibleTeamsFor(User $user): BelongsToMany
    {
        return $user->teams();
    }
}
