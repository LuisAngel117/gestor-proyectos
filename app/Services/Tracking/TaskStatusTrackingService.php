<?php

namespace App\Services\Tracking;

use App\Models\Task;
use App\Models\TaskStatusEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskStatusTrackingService
{
    public const DONE_STATUSES = ['hecho'];

    public function recordTransition(Task $task, string $toStatus, User $actor, ?Carbon $when = null): Task
    {
        if ($task->status === $toStatus) {
            return $task;
        }

        $when = $when ?? now();
        $fromStatus = $task->status;

        return DB::transaction(function () use ($task, $toStatus, $fromStatus, $actor, $when) {
            $task->update([
                'status' => $toStatus,
                'status_changed_at' => $when,
                'completed_at' => $this->shouldSetCompletedAt($fromStatus, $toStatus)
                    ? ($task->completed_at ?? $when)
                    : null,
            ]);

            TaskStatusEvent::create([
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => $actor->id,
                'changed_at' => $when,
            ]);

            return $task->refresh();
        });
    }

    private function shouldSetCompletedAt(string $fromStatus, string $toStatus): bool
    {
        if (in_array($toStatus, self::DONE_STATUSES, true)) {
            return true;
        }

        if (in_array($fromStatus, self::DONE_STATUSES, true)) {
            return false;
        }

        return false;
    }
}
