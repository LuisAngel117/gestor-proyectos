<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'apellido',
        'email',
        'password',
        'role',
        'estado',
        'avatar_path',
        'last_login_at',
        'must_change_password',
        'profile_completed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'profile_completed_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the profile associated with the user.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * The teams that the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * The teams owned by the user.
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * The projects that the user belongs to.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * The tasks assigned to the user.
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * The projects owned by the user.
     */
    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->apellido}";
    }

    /**
     * Check if the user is a superadmin.
     */
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Update the user's last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Determine if the user must finish the first-login setup.
     */
    public function requiresFirstLoginSetup(): bool
    {
        return (bool) $this->must_change_password || is_null($this->profile_completed_at);
    }

    // ==========================================
    // ACL HELPERS (M-05)
    // ==========================================

    /**
     * Check if the user has a specific global role.
     */
    public function hasGlobalRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user belongs to a specific team.
     */
    public function belongsToTeam(int $teamId): bool
    {
        return $this->teams()->where('team_id', $teamId)->exists();
    }

    /**
     * Get the user's role in a specific team.
     */
    public function roleInTeam(int $teamId): ?string
    {
        $membership = $this->teams()->where('team_id', $teamId)->first();
        return $membership ? $membership->pivot->role : null;
    }

    /**
     * Get the user's role in a specific project.
     */
    public function roleInProject(int $projectId): ?string
    {
        $membership = $this->projects()->where('project_id', $projectId)->first();
        return $membership ? $membership->pivot->role : null;
    }

    /**
     * Check if the user is owner of a specific team.
     */
    public function isOwnerOfTeam(int $teamId): bool
    {
        return $this->roleInTeam($teamId) === 'owner';
    }

    /**
     * Check if the user is admin of a specific team.
     */
    public function isAdminOfTeam(int $teamId): bool
    {
        $role = $this->roleInTeam($teamId);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if the user is owner of a specific project.
     */
    public function isOwnerOfProject(int $projectId): bool
    {
        return $this->roleInProject($projectId) === 'owner';
    }

    /**
     * Check if the user is admin of a specific project.
     */
    public function isAdminOfProject(int $projectId): bool
    {
        $role = $this->roleInProject($projectId);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if the user can perform an action on a project.
     * This is a helper that checks both global and project-level permissions.
     */
    public function canInProject(int $projectId, string $action): bool
    {
        // Superadmin can do anything
        if ($this->isSuperadmin()) {
            return true;
        }

        $role = $this->roleInProject($projectId);

        // Define role capabilities
        $capabilities = [
            'owner' => ['*'], // All actions
            'admin' => [
                'view', 'update', 'manageMembers', 'manageSprints',
                'manageTasks', 'comment', 'registerTime', 'attachFiles',
                'export', 'startSprint', 'closeSprint'
            ],
            'member' => [
                'view', 'comment', 'registerTime', 'attachFiles',
                'createTask', 'updateOwnTask', 'export'
            ],
            'observer' => ['view', 'export'],
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
}
