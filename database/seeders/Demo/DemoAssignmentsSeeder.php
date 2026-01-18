<?php

namespace Database\Seeders\Demo;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DemoAssignmentsSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::query()
            ->whereIn('slug', ['proyecto-demo-a', 'proyecto-demo-b', 'proyecto-demo-c'])
            ->get();

        foreach ($projects as $project) {
            $eligible = $project->members()
                ->wherePivotIn('role', ['owner', 'admin', 'member'])
                ->get();

            if ($eligible->isEmpty()) {
                continue;
            }

            $owner = $project->members()->wherePivot('role', 'owner')->first();
            $actorId = $owner?->id ?? $eligible->first()->id;

            $tasks = Task::where('project_id', $project->id)->orderBy('id')->get();

            foreach ($tasks as $index => $task) {
                if ($index % 7 === 0) {
                    continue;
                }

                $primary = $eligible[$index % $eligible->count()];
                $assignedAt = $task->created_at->copy()->addHours(2);

                $pivotData = [
                    $primary->id => [
                        'assigned_by' => $actorId,
                        'assigned_at' => $assignedAt,
                    ],
                ];

                if ($index % 3 === 0 && $eligible->count() > 1) {
                    $secondary = $eligible[($index + 1) % $eligible->count()];
                    if ($secondary->id !== $primary->id) {
                        $pivotData[$secondary->id] = [
                            'assigned_by' => $actorId,
                            'assigned_at' => $assignedAt->copy()->addMinutes(15),
                        ];
                    }
                }

                $task->assignees()->syncWithoutDetaching($pivotData);
            }
        }

        $this->command->info('Demo: asignaciones creadas.');
    }
}
