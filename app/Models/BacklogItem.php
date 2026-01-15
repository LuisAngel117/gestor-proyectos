<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BacklogItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'sprint_id',
        'name',
        'description',
        'priority',
        'status',
        'position',
        'sprint_position',
        'created_by',
    ];

    /**
     * Get the project that owns the backlog item.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the sprint that owns the backlog item.
     */
    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    /**
     * Get the user who created the backlog item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope items not assigned to a sprint.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('sprint_id');
    }

    /**
     * Scope items assigned to a sprint.
     */
    public function scopeAssignedToSprint($query, int $sprintId)
    {
        return $query->where('sprint_id', $sprintId);
    }
}
