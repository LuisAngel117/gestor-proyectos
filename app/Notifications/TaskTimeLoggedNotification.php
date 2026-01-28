<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\User;
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
        $actor = User::query()
            ->select('id', 'name', 'apellido')
            ->find($this->actorId);
        $actorName = $actor?->full_name ?? 'Un usuario';

        return [
            'event' => 'task_time_logged',
            'title' => 'Tiempo registrado',
            'body' => $actorName . ' registro tiempo en una tarea.',
            'project_id' => $this->task->project_id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'time_entry_id' => $this->entry->id,
            'user_id' => $this->actorId,
            'actor_name' => $actor?->full_name,
            'duration_seconds' => (int) ($this->entry->duration_seconds ?? 0),
            'source' => $this->entry->source,
            'occurred_at' => now()->toISOString(),
        ];
    }
}