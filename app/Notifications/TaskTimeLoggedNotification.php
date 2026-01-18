<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskTimeEntry;
use Illuminate\Notifications\Notification;

class TaskTimeLoggedNotification extends Notification
{
    public function __construct(
        private Task $task,
        private TaskTimeEntry $entry,
        private int $actorId
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event' => 'task_time_logged',
            'project_id' => $this->task->project_id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'time_entry_id' => $this->entry->id,
            'user_id' => $this->actorId,
            'duration_seconds' => (int) ($this->entry->duration_seconds ?? 0),
            'source' => $this->entry->source,
            'occurred_at' => now()->toISOString(),
        ];
    }
}
