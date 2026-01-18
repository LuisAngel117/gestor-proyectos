<?php

namespace Database\Seeders\Demo;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskTimeLoggedNotification;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoNotificationsSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::where('slug', 'proyecto-demo-a')->first();
        if (!$project) {
            $this->command->warn('Demo: proyecto demo no encontrado para notificaciones.');
            return;
        }

        $task = Task::where('project_id', $project->id)->first();
        if ($task) {
            $assignee = $task->assignees()->first();
            $actor = User::where('email', 'admin@gestor.test')->first();

            if ($assignee && $actor) {
                $assignee->notify(new TaskAssignedNotification($task, $actor->id, Carbon::now()->subHour()));
            }
        }

        $entry = TaskTimeEntry::query()->first();
        if ($entry) {
            $task = $entry->task;
            $creator = $task?->creator;

            if ($creator && $creator->id !== $entry->user_id) {
                $creator->notify(new TaskTimeLoggedNotification($task, $entry, $entry->user_id));
            }
        }

        $this->command->info('Demo: notificaciones creadas.');
    }
}
