<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'description',
        'status',
        'priority',
        'start_date',
        'due_date',
        'estimated_hours',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generar slug automáticamente si no se proporciona
        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);

                // Asegurar que el slug sea único dentro del equipo
                $count = 1;
                $originalSlug = $project->slug;
                while (static::where('team_id', $project->team_id)
                    ->where('slug', $project->slug)
                    ->exists()) {
                    $project->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }

    /**
     * Get the team that owns the project.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who created the project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The users that are members of the project.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get sprints for the project.
     */
    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class);
    }

    /**
     * Get backlog items for the project.
     */
    public function backlogItems(): HasMany
    {
        return $this->hasMany(BacklogItem::class);
    }

    /**
     * Get tasks for the project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the owner of the project.
     */
    public function owner()
    {
        return $this->members()->wherePivot('role', 'owner')->first();
    }

    /**
     * Get admins of the project.
     */
    public function admins()
    {
        return $this->members()->wherePivot('role', 'admin');
    }

    /**
     * Get regular members of the project.
     */
    public function regularMembers()
    {
        return $this->members()->wherePivot('role', 'member');
    }

    /**
     * Get observers of the project.
     */
    public function observers()
    {
        return $this->members()->wherePivot('role', 'observer');
    }

    /**
     * Check if a user is a member of the project.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the role of a user in the project.
     */
    public function getUserRole(User $user): ?string
    {
        $pivot = $this->members()->where('user_id', $user->id)->first();
        return $pivot ? $pivot->pivot->role : null;
    }

    /**
     * Check if a user is the owner of the project.
     */
    public function isOwner(User $user): bool
    {
        return $this->getUserRole($user) === 'owner';
    }

    /**
     * Add a member to the project with a specific role.
     */
    public function addMember(User $user, string $role = 'member'): void
    {
        if (!$this->hasMember($user)) {
            $this->members()->attach($user->id, [
                'role' => $role,
                'joined_at' => now(),
            ]);
        }
    }

    /**
     * Remove a member from the project.
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    /**
     * Update a user's role in the project.
     */
    public function updateMemberRole(User $user, string $role): void
    {
        $this->members()->updateExistingPivot($user->id, ['role' => $role]);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'planificacion' => 'Planificación',
            'en_progreso' => 'En Progreso',
            'en_espera' => 'En Espera',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
            'archivado' => 'Archivado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get the priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        $labels = [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'planificacion' => 'info',
            'en_progreso' => 'primary',
            'en_espera' => 'warning',
            'completado' => 'success',
            'cancelado' => 'danger',
            'archivado' => 'secondary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get the priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        $colors = [
            'baja' => 'success',
            'media' => 'info',
            'alta' => 'warning',
            'urgente' => 'danger',
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    /**
     * Check if the project is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !in_array($this->status, ['completado', 'cancelado']);
    }

    /**
     * Check if the project is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['planificacion', 'en_progreso']);
    }

    /**
     * Count members by role.
     */
    public function countMembersByRole(): array
    {
        $counts = $this->members()
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
