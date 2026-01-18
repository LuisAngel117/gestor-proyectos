<?php

namespace Database\Seeders\Demo;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::query()
            ->whereIn('slug', ['proyecto-demo-a', 'proyecto-demo-b', 'proyecto-demo-c'])
            ->get();

        if ($projects->isEmpty()) {
            $this->command->warn('Demo: proyectos demo no encontrados para checklist.');
            return;
        }

        foreach ($projects as $project) {
            $creator = User::find($project->created_by);
            $createdBy = $creator?->id ?? User::where('email', 'admin@gestor.test')->value('id');

            $tasks = Task::where('project_id', $project->id)
                ->orderBy('id')
                ->take(6)
                ->get();

            foreach ($tasks as $index => $task) {
                if ($task->checklistItems()->exists()) {
                    continue;
                }

                $task->checklistItems()->create([
                    'content' => 'Checklist demo 1',
                    'position' => 1,
                    'is_completed' => false,
                    'created_by' => $createdBy,
                ]);

                $task->checklistItems()->create([
                    'content' => 'Checklist demo 2',
                    'position' => 2,
                    'is_completed' => $index % 2 === 0,
                    'completed_at' => $index % 2 === 0 ? now() : null,
                    'completed_by' => $index % 2 === 0 ? $createdBy : null,
                    'created_by' => $createdBy,
                ]);
            }
        }

        $this->command->info('Demo: checklist items creados.');
    }
}
