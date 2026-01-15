<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sprint extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'name',
        'goal',
        'sequence',
        'start_date',
        'end_date',
        'status',
        'started_at',
        'closed_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the project that owns the sprint.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created the sprint.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the backlog items assigned to the sprint.
     */
    public function backlogItems(): HasMany
    {
        return $this->hasMany(BacklogItem::class)->orderBy('sprint_position');
    }

    public function isPlanning(): bool
    {
        return $this->status === 'planificacion';
    }

    public function isActive(): bool
    {
        return $this->status === 'activo';
    }

    public function isClosed(): bool
    {
        return $this->status === 'cerrado';
    }
}
