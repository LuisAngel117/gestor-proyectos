<?php

namespace Database\Seeders\Demo;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskStatusEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DemoTasksSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::query()
            ->whereIn('slug', ['proyecto-demo-a', 'proyecto-demo-b', 'proyecto-demo-c'])
            ->get();

        if ($projects->isEmpty()) {
            $this->command->warn('Demo: proyectos demo no encontrados.');
            return;
        }

        Model::unguard();

        foreach ($projects as $project) {
            $activeSprint = Sprint::where('project_id', $project->id)
                ->where('status', 'activo')
                ->first();
            $closedSprint = Sprint::where('project_id', $project->id)
                ->where('status', 'cerrado')
                ->first();

            if (!$activeSprint || !$closedSprint) {
                continue;
            }

            $creatorId = $project->created_by;
            $statusPattern = [
                'todo', 'todo', 'todo', 'todo',
                'en_progreso', 'en_progreso', 'en_progreso', 'en_progreso', 'en_progreso',
                'hecho', 'hecho', 'hecho',
            ];

            $tasks = [];
            $parentIds = [];

            for ($i = 1; $i <= 20; $i++) {
                $isActive = $i <= 12;
                $isClosed = $i > 12 && $i <= 17;
                $isBacklog = $i > 17;

                $sprintId = $isActive ? $activeSprint->id : ($isClosed ? $closedSprint->id : null);
                $status = $isActive ? $statusPattern[$i - 1] : ($isClosed ? 'hecho' : 'todo');

                if ($isActive) {
                    $start = Carbon::parse($activeSprint->start_date);
                    $createdAt = $start->copy()->addDays(($i - 1) % 5)->setTime(9, 0);
                } elseif ($isClosed) {
                    $start = Carbon::parse($closedSprint->start_date);
                    $createdAt = $start->copy()->addDays(($i - 1) % 5)->setTime(9, 0);
                } else {
                    $start = now()->subDays(2);
                    $createdAt = $start->copy()->setTime(9, 0);
                }

                $dueDate = $isBacklog
                    ? ($i % 2 === 0 ? now()->addDays(10)->toDateString() : null)
                    : $start->copy()->addDays(($i - 1) % 10)->toDateString();

                $priorityOptions = ['baja', 'media', 'alta', 'urgente'];
                $priority = $priorityOptions[($i - 1) % count($priorityOptions)];

                $task = Task::create([
                    'project_id' => $project->id,
                    'sprint_id' => $sprintId,
                    'title' => sprintf('Tarea %s-%02d', strtoupper($project->slug[-1]), $i),
                    'description' => 'Tarea demo generada para QA.',
                    'status' => $status,
                    'priority' => $priority,
                    'estimated_hours' => ($i % 6) + 2,
                    'due_date' => $dueDate,
                    'created_by' => $creatorId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($i <= 3) {
                    $parentIds[] = $task->id;
                }

                $tasks[] = $task;
            }

            foreach ($tasks as $index => $task) {
                if ($index >= 3 && $index <= 5 && isset($parentIds[$index - 3])) {
                    $task->update(['parent_id' => $parentIds[$index - 3]]);
                }
            }

            $this->seedStatusEvents($tasks, $project);
        }

        Model::reguard();

        $this->command->info('Demo: tareas demo creadas.');
    }

    private function seedStatusEvents(array $tasks, Project $project): void
    {
        $actorId = $project->created_by ?? User::where('email', 'admin@gestor.test')->value('id');

        foreach ($tasks as $task) {
            if ($task->status === 'todo') {
                $task->update(['status_changed_at' => $task->created_at]);
                continue;
            }

            $createdAt = Carbon::parse($task->created_at);
            $inProgressAt = $createdAt->copy()->addDays(1);

            if ($task->status === 'en_progreso') {
                $task->update(['status_changed_at' => $inProgressAt]);
                TaskStatusEvent::create([
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'from_status' => 'todo',
                    'to_status' => 'en_progreso',
                    'changed_by' => $actorId,
                    'changed_at' => $inProgressAt,
                ]);
                continue;
            }

            if ($task->status === 'hecho') {
                $doneAt = $createdAt->copy()->addDays(2);

                TaskStatusEvent::create([
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'from_status' => 'todo',
                    'to_status' => 'en_progreso',
                    'changed_by' => $actorId,
                    'changed_at' => $inProgressAt,
                ]);

                TaskStatusEvent::create([
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'from_status' => 'en_progreso',
                    'to_status' => 'hecho',
                    'changed_by' => $actorId,
                    'changed_at' => $doneAt,
                ]);

                $task->update([
                    'status_changed_at' => $doneAt,
                    'completed_at' => $doneAt,
                ]);
            }
        }
    }
}
