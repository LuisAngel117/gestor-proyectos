<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Notifications\Notification;

class TaskTimerStartedNotification extends Notification
{
    public function __construct(
        private Task $task,
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
        $actorName = $actor?->full_name ?? 'Un miembro';

        return [
            'event' => 'task_timer_started',
            'title' => 'Timer iniciado',
            'body' => $actorName . ' inicio el timer de una tarea.',
            'project_id' => $this->task->project_id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'user_id' => $this->actorId,
            'actor_name' => $actor?->full_name,
            'occurred_at' => now()->toISOString(),
        ];
    }
}