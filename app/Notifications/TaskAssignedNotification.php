<?php

namespace App\Notifications;

use App\Models\Task;
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
        return [
            'event' => 'task_assigned',
            'project_id' => $this->task->project_id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'assigned_by' => $this->assignedBy,
            'assigned_at' => $this->assignedAt->toISOString(),
            'occurred_at' => now()->toISOString(),
        ];
    }
}
