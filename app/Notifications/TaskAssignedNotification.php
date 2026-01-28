<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    public function __construct(
        private Task $task,
        private ?int $assignedBy,
        private Carbon $assignedAt
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $actor = $this->assignedBy
            ? User::query()->select('id', 'name', 'apellido')->find($this->assignedBy)
            : null;
        $actorName = $actor?->full_name ?? 'Un usuario';

        return [
            'event' => 'task_assigned',
            'title' => 'Tarea asignada',
            'body' => $actorName . ' te asigno una tarea.',
            'project_id' => $this->task->project_id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'assigned_by' => $this->assignedBy,
            'actor_name' => $actor?->full_name,
            'assigned_at' => $this->assignedAt->toISOString(),
            'occurred_at' => now()->toISOString(),
        ];
    }
}