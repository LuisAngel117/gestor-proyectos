<?php

namespace Database\Seeders\Demo;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSprintsSeeder extends Seeder
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

        foreach ($projects as $project) {
            $creator = User::find($project->created_by);
            $createdBy = $creator?->id ?? User::where('email', 'admin@gestor.test')->value('id');

            $closedStart = now()->subDays(30);
            $closedEnd = now()->subDays(16);
            $activeStart = now()->subDays(7);
            $activeEnd = now()->addDays(7);
            $planningStart = now()->addDays(1);
            $planningEnd = now()->addDays(14);

            Sprint::updateOrCreate(
                ['project_id' => $project->id, 'name' => 'Sprint Cerrado'],
                [
                    'goal' => 'Sprint cerrado demo',
                    'sequence' => 1,
                    'start_date' => $closedStart->toDateString(),
                    'end_date' => $closedEnd->toDateString(),
                    'status' => 'cerrado',
                    'started_at' => $closedStart,
                    'closed_at' => $closedEnd,
                    'created_by' => $createdBy,
                ]
            );

            Sprint::updateOrCreate(
                ['project_id' => $project->id, 'name' => 'Sprint Activo'],
                [
                    'goal' => 'Sprint activo demo',
                    'sequence' => 2,
                    'start_date' => $activeStart->toDateString(),
                    'end_date' => $activeEnd->toDateString(),
                    'status' => 'activo',
                    'started_at' => $activeStart,
                    'closed_at' => null,
                    'created_by' => $createdBy,
                ]
            );

            Sprint::updateOrCreate(
                ['project_id' => $project->id, 'name' => 'Sprint Planificacion'],
                [
                    'goal' => 'Sprint en planificacion demo',
                    'sequence' => 3,
                    'start_date' => $planningStart->toDateString(),
                    'end_date' => $planningEnd->toDateString(),
                    'status' => 'planificacion',
                    'started_at' => null,
                    'closed_at' => null,
                    'created_by' => $createdBy,
                ]
            );
        }

        $this->command->info('Demo: sprints activos, cerrados y en planificacion listos.');
    }
}
