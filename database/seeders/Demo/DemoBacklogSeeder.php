<?php

namespace Database\Seeders\Demo;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoBacklogSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::query()
            ->whereIn('slug', ['proyecto-demo-a', 'proyecto-demo-b', 'proyecto-demo-c'])
            ->get();

        if ($projects->isEmpty()) {
            $this->command->warn('Demo: proyectos demo no encontrados para backlog.');
            return;
        }

        $priorities = array_keys(config('catalogs.projects.priorities', ['media']));

        foreach ($projects as $project) {
            $creator = User::find($project->created_by);
            $createdBy = $creator?->id ?? User::where('email', 'admin@gestor.test')->value('id');
            $planningSprint = Sprint::where('project_id', $project->id)
                ->where('status', 'planificacion')
                ->first();

            $position = (int) $project->backlogItems()->max('position');

            for ($i = 1; $i <= 8; $i++) {
                $position++;
                $priority = $priorities[($i - 1) % count($priorities)];
                $name = sprintf('Backlog %s-%02d', strtoupper($project->slug[-1]), $i);

                $payload = [
                    'project_id' => $project->id,
                    'sprint_id' => null,
                    'name' => $name,
                    'description' => 'Backlog demo para planificacion.',
                    'priority' => $priority,
                    'status' => 'backlog',
                    'position' => $position,
                    'sprint_position' => null,
                    'created_by' => $createdBy,
                ];

                if ($planningSprint && $i <= 3) {
                    $payload['sprint_id'] = $planningSprint->id;
                    $payload['sprint_position'] = $i;
                }

                BacklogItem::updateOrCreate(
                    ['project_id' => $project->id, 'name' => $name],
                    $payload
                );
            }
        }

        $this->command->info('Demo: backlog items creados.');
    }
}
