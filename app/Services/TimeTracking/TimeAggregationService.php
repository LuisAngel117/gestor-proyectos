<?php

namespace App\Services\TimeTracking;

use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use Carbon\Carbon;

class TimeAggregationService
{
    public const OUTLIER_THRESHOLD_SECONDS = 43200;

    public function taskSummary(Task $task, bool $includeSubtasks, bool $includeRunning): array
    {
        $taskIds = $this->taskIdsForTask($task, $includeSubtasks);
        $ownSeconds = $this->sumClosedForTasks([$task->id]);
        $rollupSeconds = $includeSubtasks ? $this->sumClosedForTasks($taskIds) : $ownSeconds;

        $summary = [
            'task_id' => $task->id,
            'own_seconds' => $ownSeconds,
            'own_hours' => $this->toHours($ownSeconds),
            'rollup_seconds' => $rollupSeconds,
            'rollup_hours' => $this->toHours($rollupSeconds),
        ];

        if ($includeRunning) {
            $summary['running_seconds'] = $this->sumRunningForTasks([$task->id]);
        }

        $warnings = $this->outlierWarningsForTasks($taskIds);
        if (!empty($warnings)) {
            $summary['warnings'] = $warnings;
        }

        return $summary;
    }

    public function sprintSummary(Sprint $sprint, bool $includeRunning, bool $groupByUser): array
    {
        $taskIds = $sprint->tasks()
            ->where('project_id', $sprint->project_id)
            ->pluck('id')
            ->all();

        $totalSeconds = $this->sumClosedForTasks($taskIds);

        $summary = [
            'project_id' => $sprint->project_id,
            'sprint_id' => $sprint->id,
            'total_seconds' => $totalSeconds,
            'total_hours' => $this->toHours($totalSeconds),
        ];

        if ($includeRunning) {
            $summary['running_seconds'] = $this->sumRunningForTasks($taskIds);
        }

        if ($groupByUser) {
            $summary['by_user'] = $this->groupClosedByUser($taskIds);
        }

        $warnings = $this->outlierWarningsForTasks($taskIds);
        if (!empty($warnings)) {
            $summary['warnings'] = $warnings;
        }

        return $summary;
    }

    private function taskIdsForTask(Task $task, bool $includeSubtasks): array
    {
        $taskIds = [$task->id];
        if ($includeSubtasks) {
            $taskIds = array_merge(
                $taskIds,
                $task->subtasks()->pluck('id')->all()
            );
        }

        return $taskIds;
    }

    private function sumClosedForTasks(array $taskIds): int
    {
        if (empty($taskIds)) {
            return 0;
        }

        return (int) TaskTimeEntry::query()
            ->whereIn('task_id', $taskIds)
            ->whereNotNull('stopped_at')
            ->whereNotNull('duration_seconds')
            ->where('duration_seconds', '>', 0)
            ->sum('duration_seconds');
    }

    private function groupClosedByUser(array $taskIds): array
    {
        if (empty($taskIds)) {
            return [];
        }

        return TaskTimeEntry::query()
            ->whereIn('task_id', $taskIds)
            ->whereNotNull('stopped_at')
            ->whereNotNull('duration_seconds')
            ->where('duration_seconds', '>', 0)
            ->selectRaw('user_id, SUM(duration_seconds) as total_seconds')
            ->groupBy('user_id')
            ->orderBy('user_id')
            ->get()
            ->map(function (TaskTimeEntry $entry) {
                $seconds = (int) $entry->total_seconds;

                return [
                    'user_id' => $entry->user_id,
                    'total_seconds' => $seconds,
                    'total_hours' => $this->toHours($seconds),
                ];
            })
            ->all();
    }

    private function sumRunningForTasks(array $taskIds): int
    {
        if (empty($taskIds)) {
            return 0;
        }

        $now = Carbon::now();
        $entries = TaskTimeEntry::query()
            ->whereIn('task_id', $taskIds)
            ->whereNull('stopped_at')
            ->get(['started_at']);

        $seconds = 0;
        foreach ($entries as $entry) {
            if (!$entry->started_at) {
                continue;
            }
            $seconds += max(0, $now->diffInSeconds(Carbon::parse($entry->started_at)));
        }

        return $seconds;
    }

    private function outlierWarningsForTasks(array $taskIds): array
    {
        if (empty($taskIds)) {
            return [];
        }

        $outlierCount = TaskTimeEntry::query()
            ->whereIn('task_id', $taskIds)
            ->whereNotNull('stopped_at')
            ->whereNotNull('duration_seconds')
            ->where('duration_seconds', '>', self::OUTLIER_THRESHOLD_SECONDS)
            ->count();

        if ($outlierCount === 0) {
            return [];
        }

        return [
            'outliers' => [
                'count' => $outlierCount,
                'threshold_seconds' => self::OUTLIER_THRESHOLD_SECONDS,
            ],
        ];
    }

    private function toHours(int $seconds): float
    {
        return round($seconds / 3600, 2);
    }
}
