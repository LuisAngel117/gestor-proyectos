<?php

namespace App\Services\Dashboard;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskStatusEvent;
use App\Models\TaskTimeEntry;
use App\Services\Boards\ScrumBoardService;
use App\Services\Tracking\TaskStatusTrackingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectDashboardMetricsService
{
    public function build(Project $project, Sprint $sprint, ?Sprint $previousSprint = null): array
    {
        $velocity = $this->velocity($project, $sprint);
        $previousVelocity = $previousSprint ? $this->velocity($project, $previousSprint) : null;

        return [
            'velocity' => $velocity,
            'previous_velocity' => $previousVelocity,
            'time_in_state' => $this->timeInState($project, $sprint),
            'workload' => $this->workload($project, $sprint),
        ];
    }

    public function velocity(Project $project, Sprint $sprint): array
    {
        $query = Task::query()
            ->where('project_id', $project->id)
            ->where('sprint_id', $sprint->id)
            ->whereNotNull('completed_at');

        $range = $this->sprintRange($sprint);
        if ($range['start']) {
            $query->whereDate('completed_at', '>=', $range['start']->toDateString());
        }
        if ($range['end']) {
            $query->whereDate('completed_at', '<=', $range['end']->toDateString());
        }

        $completedCount = (clone $query)->count();
        $totalHours = (float) (clone $query)->sum('estimated_hours');

        return [
            'sprint_id' => $sprint->id,
            'completed_count' => $completedCount,
            'total_hours' => $this->roundHours($totalHours),
        ];
    }

    public function timeInState(Project $project, Sprint $sprint): array
    {
        $statuses = ScrumBoardService::STATUSES;
        $statusKeys = array_keys($statuses);
        $warnings = [];

        $range = $this->sprintRange($sprint);
        $windowStart = $range['start'] ?? $sprint->created_at;
        $windowEnd = $range['end'] ?? now();

        $tasks = Task::query()
            ->where('project_id', $project->id)
            ->where('sprint_id', $sprint->id)
            ->get(['id', 'title', 'status', 'created_at', 'status_changed_at', 'updated_at']);

        if ($tasks->isEmpty()) {
            return [
                'statuses' => $statuses,
                'summaries' => $this->emptyStatusSummaries($statusKeys),
                'oldest' => $this->emptyOldestLists($statusKeys),
                'warnings' => [],
            ];
        }

        $events = TaskStatusEvent::query()
            ->whereIn('task_id', $tasks->pluck('id'))
            ->where('changed_at', '<=', $windowEnd)
            ->orderBy('changed_at')
            ->get()
            ->groupBy('task_id');

        $totals = array_fill_keys($statusKeys, 0);
        $counts = array_fill_keys($statusKeys, 0);
        $oldest = $this->emptyOldestLists($statusKeys);

        foreach ($tasks as $task) {
            $perTask = array_fill_keys($statusKeys, 0);
            $taskEvents = $events->get($task->id, collect());

            $initialStatus = $taskEvents->first()?->from_status ?? $task->status;
            $initialStatus = $this->normalizeStatus($initialStatus, $statusKeys, $warnings);

            $lastStatus = $initialStatus;
            if ($taskEvents->isEmpty()) {
                $startTime = $task->status_changed_at ?? $task->created_at;
            } else {
                $startTime = $task->created_at;
            }
            $lastTime = $startTime instanceof Carbon
                ? $startTime
                : Carbon::parse($startTime);

            foreach ($taskEvents as $event) {
                $eventTime = $event->changed_at instanceof Carbon
                    ? $event->changed_at
                    : Carbon::parse($event->changed_at);

                $this->addOverlap($perTask, $lastStatus, $lastTime, $eventTime, $windowStart, $windowEnd);

                $lastStatus = $this->normalizeStatus($event->to_status, $statusKeys, $warnings);
                $lastTime = $eventTime;
            }

            $this->addOverlap($perTask, $lastStatus, $lastTime, $windowEnd, $windowStart, $windowEnd);

            foreach ($statusKeys as $status) {
                $duration = $perTask[$status];
                if ($duration > 0) {
                    $totals[$status] += $duration;
                    $counts[$status]++;
                }
            }

            $currentStatus = $this->normalizeStatus($task->status, $statusKeys, $warnings);
            $changedAt = $task->status_changed_at ?? $task->updated_at ?? $task->created_at;
            $ageSeconds = $this->diffInSeconds($changedAt, now());

            $oldest[$currentStatus][] = [
                'task_id' => $task->id,
                'title' => $task->title,
                'age_seconds' => $ageSeconds,
            ];
        }

        foreach ($statusKeys as $status) {
            usort($oldest[$status], function (array $a, array $b) {
                return $b['age_seconds'] <=> $a['age_seconds'];
            });
            $oldest[$status] = array_slice($oldest[$status], 0, 5);
        }

        $summaries = [];
        foreach ($statusKeys as $status) {
            $count = $counts[$status];
            $avgSeconds = $count > 0 ? (int) round($totals[$status] / $count) : 0;
            $summaries[$status] = [
                'total_seconds' => $totals[$status],
                'avg_seconds' => $avgSeconds,
                'avg_hours' => $this->roundHours($avgSeconds / 3600),
                'task_count' => $count,
            ];
        }

        return [
            'statuses' => $statuses,
            'summaries' => $summaries,
            'oldest' => $oldest,
            'warnings' => $warnings,
        ];
    }

    public function workload(Project $project, Sprint $sprint): array
    {
        $members = $project->members()
            ->select('users.id', 'users.name', 'users.apellido')
            ->orderBy('users.name')
            ->get();

        $openTasks = Task::query()
            ->where('project_id', $project->id)
            ->where('sprint_id', $sprint->id)
            ->whereNotIn('status', TaskStatusTrackingService::DONE_STATUSES)
            ->with(['assignees' => function ($query) {
                $query->select('users.id', 'users.name', 'users.apellido');
            }])
            ->get(['id', 'title', 'estimated_hours']);

        $plannedByUser = [];
        $plannedTotals = [
            'total_hours' => 0.0,
            'total_tasks' => 0,
        ];

        foreach ($openTasks as $task) {
            $assignees = $task->assignees->sortBy(function ($user) {
                return $user->pivot?->assigned_at;
            });

            $ownerId = $assignees->isEmpty() ? null : $assignees->first()->id;
            $key = $ownerId ? (string) $ownerId : 'unassigned';

            if (!isset($plannedByUser[$key])) {
                $plannedByUser[$key] = [
                    'user_id' => $ownerId,
                    'tasks' => 0,
                    'hours' => 0.0,
                ];
            }

            $hours = (float) ($task->estimated_hours ?? 0);
            $plannedByUser[$key]['tasks']++;
            $plannedByUser[$key]['hours'] += $hours;
            $plannedTotals['total_hours'] += $hours;
            $plannedTotals['total_tasks']++;
        }

        $realByUser = TaskTimeEntry::query()
            ->join('tasks', 'tasks.id', '=', 'task_time_entries.task_id')
            ->where('tasks.project_id', $project->id)
            ->where('tasks.sprint_id', $sprint->id)
            ->whereNotNull('task_time_entries.stopped_at')
            ->where('task_time_entries.duration_seconds', '>', 0)
            ->groupBy('task_time_entries.user_id')
            ->pluck(DB::raw('SUM(task_time_entries.duration_seconds) as total_seconds'), 'task_time_entries.user_id')
            ->toArray();

        $rows = [];
        foreach ($members as $member) {
            $key = (string) $member->id;
            $planned = $plannedByUser[$key] ?? ['tasks' => 0, 'hours' => 0.0];
            $realSeconds = (int) ($realByUser[$member->id] ?? 0);

            $rows[] = [
                'user_id' => $member->id,
                'label' => trim($member->name . ' ' . $member->apellido),
                'planned_hours' => $this->roundHours($planned['hours']),
                'planned_tasks' => $planned['tasks'],
                'real_hours' => $this->roundHours($realSeconds / 3600),
                'real_seconds' => $realSeconds,
            ];
        }

        if (isset($plannedByUser['unassigned'])) {
            $unassigned = $plannedByUser['unassigned'];
            $rows[] = [
                'user_id' => null,
                'label' => 'Sin asignar',
                'planned_hours' => $this->roundHours($unassigned['hours']),
                'planned_tasks' => $unassigned['tasks'],
                'real_hours' => 0.0,
                'real_seconds' => 0,
            ];
        }

        return [
            'rows' => $rows,
            'totals' => [
                'planned_hours' => $this->roundHours($plannedTotals['total_hours']),
                'planned_tasks' => $plannedTotals['total_tasks'],
                'real_hours' => $this->roundHours(array_sum($realByUser) / 3600),
                'real_seconds' => array_sum($realByUser),
            ],
        ];
    }

    private function sprintRange(Sprint $sprint): array
    {
        $start = $sprint->start_date
            ? Carbon::parse($sprint->start_date)->startOfDay()
            : null;

        $end = $sprint->end_date
            ? Carbon::parse($sprint->end_date)->endOfDay()
            : null;

        return [
            'start' => $start,
            'end' => $end ?? now(),
        ];
    }

    private function emptyStatusSummaries(array $statusKeys): array
    {
        $summaries = [];
        foreach ($statusKeys as $status) {
            $summaries[$status] = [
                'total_seconds' => 0,
                'avg_seconds' => 0,
                'avg_hours' => 0.0,
                'task_count' => 0,
            ];
        }

        return $summaries;
    }

    private function emptyOldestLists(array $statusKeys): array
    {
        $lists = [];
        foreach ($statusKeys as $status) {
            $lists[$status] = [];
        }

        return $lists;
    }

    private function addOverlap(
        array &$perTask,
        string $status,
        Carbon $segmentStart,
        Carbon $segmentEnd,
        Carbon $windowStart,
        Carbon $windowEnd
    ): void {
        if ($segmentEnd->lte($segmentStart)) {
            return;
        }

        $start = $segmentStart->greaterThan($windowStart) ? $segmentStart : $windowStart;
        $end = $segmentEnd->lessThan($windowEnd) ? $segmentEnd : $windowEnd;

        if ($end->lte($start)) {
            return;
        }

        $perTask[$status] += $end->diffInSeconds($start);
    }

    private function normalizeStatus(string $status, array $allowed, array &$warnings): string
    {
        if (in_array($status, $allowed, true)) {
            return $status;
        }

        if (!in_array($status, $warnings, true)) {
            $warnings[] = $status;
        }

        return $allowed[0];
    }

    private function roundHours(float $hours): float
    {
        return (float) number_format($hours, 2, '.', '');
    }

    private function diffInSeconds($from, Carbon $to): int
    {
        $fromTime = $from instanceof Carbon ? $from : Carbon::parse($from);

        return $to->diffInSeconds($fromTime);
    }
}
