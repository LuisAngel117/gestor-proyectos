<?php

namespace Database\Seeders\Demo;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::where('name', 'Equipo Demo')->first();
        if (!$team) {
            $this->command->warn('Demo: equipo demo no encontrado.');
            return;
        }

        $owner = User::where('email', 'admin@gestor.test')->first();
        $admins = [
            'carlos@gestor.test',
            'maria@gestor.test',
            'juan@gestor.test',
        ];

        $projects = [
            [
                'name' => 'Proyecto Demo A',
                'status' => 'en_progreso',
                'priority' => 'alta',
                'start_date' => now()->subWeeks(6),
                'due_date' => now()->addWeeks(8),
                'estimated_hours' => 160,
            ],
            [
                'name' => 'Proyecto Demo B',
                'status' => 'planificacion',
                'priority' => 'media',
                'start_date' => now()->subWeeks(2),
                'due_date' => now()->addWeeks(10),
                'estimated_hours' => 120,
            ],
            [
                'name' => 'Proyecto Demo C',
                'status' => 'en_progreso',
                'priority' => 'alta',
                'start_date' => now()->subWeeks(4),
                'due_date' => now()->addWeeks(6),
                'estimated_hours' => 180,
            ],
        ];

        foreach ($projects as $index => $payload) {
            $adminEmail = $admins[$index] ?? $admins[0];
            $admin = User::where('email', $adminEmail)->first();

            $project = Project::updateOrCreate(
                ['team_id' => $team->id, 'slug' => Str::slug($payload['name'])],
                array_merge($payload, [
                    'team_id' => $team->id,
                    'created_by' => $owner?->id ?? $admin?->id,
                ])
            );

            if ($owner) {
                $project->addMember($owner, 'owner');
                $project->updateMemberRole($owner, 'owner');
            }

            if ($admin) {
                $project->addMember($admin, 'admin');
                $project->updateMemberRole($admin, 'admin');
            }

            $memberEmails = [
                'maria@gestor.test',
                'juan@gestor.test',
                'ana@gestor.test',
                'pedro@gestor.test',
                'laura@gestor.test',
                'diego@gestor.test',
            ];

            foreach ($memberEmails as $email) {
                $user = User::where('email', $email)->first();
                if (!$user) {
                    continue;
                }
                $project->addMember($user, 'member');
                $project->updateMemberRole($user, 'member');
            }

            foreach (['sofia@gestor.test', 'miguel@gestor.test'] as $email) {
                $user = User::where('email', $email)->first();
                if (!$user) {
                    continue;
                }
                $project->addMember($user, 'observer');
                $project->updateMemberRole($user, 'observer');
            }
        }

        $this->command->info('Demo: 3 proyectos creados.');
    }
}
