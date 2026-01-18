<?php

namespace App\Services\Calendar;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use App\Services\Boards\ScrumBoardService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProjectCalendarService
{
    public const DATE_FIELD = 'due_date';

    public function getActiveSprint(Project $project): ?Sprint
    {
        return $project->sprints()
            ->where('status', 'activo')
            ->orderByDesc('start_date')
            ->first();
    }

    public function buildMonthly(Project $project, Carbon $month, User $viewer, array $filters = []): array
    {
        $month = $month->copy()->startOfMonth();
        $rangeStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $rangeEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $tasks = $this->queryTasks($project, $rangeStart, $rangeEnd, $filters);
        $tasksByDate = $tasks->groupBy(function (Task $task) {
            return optional($task->{self::DATE_FIELD})->toDateString();
        });

        $days = [];
        $cursor = $rangeStart->copy();
        while ($cursor->lte($rangeEnd)) {
            $dateKey = $cursor->toDateString();
            $days[] = [
                'date' => $cursor->copy(),
                'is_current_month' => $cursor->month === $month->month,
                'tasks' => $tasksByDate->get($dateKey, collect()),
            ];
            $cursor->addDay();
        }

        $warnings = [];
        if (($filters['sprint_mode'] ?? null) === 'active' && empty($filters['sprint_id'])) {
            $warnings[] = 'No hay sprint activo para el filtro seleccionado.';
        }

        return [
            'month' => $month,
            'month_label' => $month->translatedFormat('F Y'),
            'month_value' => $month->format('Y-m'),
            'prev_month' => $month->copy()->subMonth()->format('Y-m'),
            'next_month' => $month->copy()->addMonth()->format('Y-m'),
            'range_start' => $rangeStart,
            'range_end' => $rangeEnd,
            'days' => $days,
            'undated_tasks' => $this->queryUndatedTasks($project, $filters),
            'filters' => $filters,
            'statuses' => ScrumBoardService::STATUSES,
            'date_field' => self::DATE_FIELD,
            'warnings' => $warnings,
        ];
    }

    public function queryTasks(Project $project, Carbon $from, Carbon $to, array $filters = []): Collection
    {
        if (($filters['sprint_mode'] ?? null) === 'active' && empty($filters['sprint_id'])) {
            return collect();
        }

        $query = Task::query()
            ->where('project_id', $project->id)
            ->whereNotNull(self::DATE_FIELD)
            ->whereBetween(self::DATE_FIELD, [$from->toDateString(), $to->toDateString()])
            ->with(['assignees' => function ($query) {
                $query->select('users.id', 'users.name', 'users.apellido');
            }])
            ->orderBy(self::DATE_FIELD)
            ->orderBy('created_at');

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function queryUndatedTasks(Project $project, array $filters = []): Collection
    {
        if (($filters['sprint_mode'] ?? null) === 'active' && empty($filters['sprint_id'])) {
            return collect();
        }

        $query = Task::query()
            ->where('project_id', $project->id)
            ->whereNull(self::DATE_FIELD)
            ->with(['assignees' => function ($query) {
                $query->select('users.id', 'users.name', 'users.apellido');
            }])
            ->orderBy('created_at');

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['sprint_mode'])) {
            if ($filters['sprint_mode'] === 'backlog') {
                $query->whereNull('sprint_id');
            } elseif (!empty($filters['sprint_id'])) {
                $query->where('sprint_id', $filters['sprint_id']);
            }
        }

        if (!empty($filters['assignee'])) {
            $assigneeId = (int) $filters['assignee'];
            $query->whereHas('assignees', function (Builder $query) use ($assigneeId) {
                $query->where('users.id', $assigneeId);
            });
        }
    }
}
