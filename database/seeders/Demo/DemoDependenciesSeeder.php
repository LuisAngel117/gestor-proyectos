<?php

namespace Database\Seeders\Demo;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DemoDependenciesSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::query()
            ->whereIn('slug', ['proyecto-demo-a', 'proyecto-demo-b', 'proyecto-demo-c'])
            ->get();

        if ($projects->isEmpty()) {
            $this->command->warn('Demo: proyectos demo no encontrados para dependencias.');
            return;
        }

        foreach ($projects as $project) {
            $tasks = Task::where('project_id', $project->id)
                ->orderBy('id')
                ->get()
                ->values();

            if ($tasks->count() < 4) {
                continue;
            }

            $pairs = [
                [2, 0],
                [3, 1],
                [5, 2],
            ];

            foreach ($pairs as [$taskIndex, $dependsIndex]) {
                if (!isset($tasks[$taskIndex], $tasks[$dependsIndex])) {
                    continue;
                }

                $task = $tasks[$taskIndex];
                $dependsOn = $tasks[$dependsIndex];

                if ($task->id === $dependsOn->id) {
                    continue;
                }

                $task->prerequisites()->syncWithoutDetaching([$dependsOn->id]);
            }
        }

        $this->command->info('Demo: dependencias creadas.');
    }
}
