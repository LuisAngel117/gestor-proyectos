<?php

namespace App\Services\TimeTracking;

use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use RuntimeException;

class TaskTimerService
{
    public function getActiveForUser(User $user): ?TaskTimeEntry
    {
        return TaskTimeEntry::query()
            ->where('user_id', $user->id)
            ->whereNull('stopped_at')
            ->latest('started_at')
            ->first();
    }

    public function getActiveForUserAndTask(User $user, Task $task): ?TaskTimeEntry
    {
        return TaskTimeEntry::query()
            ->where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->whereNull('stopped_at')
            ->latest('started_at')
            ->first();
    }

    public function start(Task $task, User $user): TaskTimeEntry
    {
        return DB::transaction(function () use ($task, $user) {
            $activeEntry = TaskTimeEntry::query()
                ->where('user_id', $user->id)
                ->whereNull('stopped_at')
                ->lockForUpdate()
                ->first();

            if ($activeEntry) {
                throw new RuntimeException('Ya tienes un temporizador activo en otra tarea.');
            }

            return TaskTimeEntry::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'duration_seconds' => 0,
                'source' => 'timer',
                'created_by' => $user->id,
            ]);
        });
    }

    public function stop(Task $task, User $user): TaskTimeEntry
    {
        return DB::transaction(function () use ($task, $user) {
            $entry = TaskTimeEntry::query()
                ->where('user_id', $user->id)
                ->where('task_id', $task->id)
                ->whereNull('stopped_at')
                ->lockForUpdate()
                ->first();

            if (!$entry) {
                throw new RuntimeException('No hay un temporizador activo para esta tarea.');
            }

            $stoppedAt = now();
            $duration = max(0, $stoppedAt->diffInSeconds(Carbon::parse($entry->started_at)));

            $entry->update([
                'stopped_at' => $stoppedAt,
                'duration_seconds' => $duration,
            ]);

            return $entry->refresh();
        });
    }
}
