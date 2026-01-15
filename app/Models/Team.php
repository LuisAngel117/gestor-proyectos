<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    /**
     * Get the owner of the team.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * The projects that belong to the team.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get members with a specific role.
     */
    public function members()
    {
        return $this->users()->wherePivot('role', 'member');
    }

    /**
     * Get admins of the team.
     */
    public function admins()
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    /**
     * Get observers of the team.
     */
    public function observers()
    {
        return $this->users()->wherePivot('role', 'observer');
    }

    /**
     * Check if a user is the owner of the team.
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Check if a user is a member of the team.
     */
    public function hasMember(User $user): bool
    {
        if ($this->owner_id === $user->id) {
            return true;
        }

        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the role of a user in the team.
     */
    public function getUserRole(User $user): ?string
    {
        if ($this->owner_id === $user->id) {
            return 'owner';
        }

        $pivot = $this->users()->where('user_id', $user->id)->first();
        return $pivot ? $pivot->pivot->role : null;
    }

    /**
     * Check if a user has a specific role in the team.
     */
    public function userHasRole(User $user, string $role): bool
    {
        return $this->getUserRole($user) === $role;
    }

    /**
     * Add a user to the team with a specific role.
     */
    public function addMember(User $user, string $role = 'member'): void
    {
        if (!$this->hasMember($user)) {
            $this->users()->attach($user->id, [
                'role' => $role,
                'joined_at' => now(),
            ]);
        }
    }

    /**
     * Remove a user from the team.
     */
    public function removeMember(User $user): void
    {
        $this->users()->detach($user->id);
    }

    /**
     * Update a user's role in the team.
     */
    public function updateMemberRole(User $user, string $role): void
    {
        $this->users()->updateExistingPivot($user->id, ['role' => $role]);
    }

    // ==========================================
    // ACL HELPERS (M-05)
    // ==========================================

    /**
     * Check if a user can perform a specific action in this team.
     */
    public function userCan(User $user, string $action): bool
    {
        // Superadmin can do anything
        if ($user->isSuperadmin()) {
            return true;
        }

        $role = $this->getUserRole($user);

        // Define role capabilities at team level
        $capabilities = [
            'owner' => ['*'], // All actions
            'admin' => [
                'view', 'update', 'manageMembers', 'createProject',
                'manageProjects', 'viewReports'
            ],
            'member' => ['view', 'viewReports'],
            'observer' => ['view'],
        ];

        if (!$role || !isset($capabilities[$role])) {
            return false;
        }

        // Owner has all permissions
        if (in_array('*', $capabilities[$role])) {
            return true;
        }

        return in_array($action, $capabilities[$role]);
    }

    /**
     * Get all members with their roles.
     */
    public function getMembersWithRoles()
    {
        return $this->users()->get()->map(function ($user) {
            return [
                'user' => $user,
                'role' => $user->pivot->role,
                'joined_at' => $user->pivot->joined_at,
            ];
        });
    }

    /**
     * Count members by role.
     */
    public function countMembersByRole(): array
    {
        $counts = $this->users()
            ->selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return [
            'owner' => $counts['owner'] ?? 0,
            'admin' => $counts['admin'] ?? 0,
            'member' => $counts['member'] ?? 0,
            'observer' => $counts['observer'] ?? 0,
            'total' => array_sum($counts),
        ];
    }
}
