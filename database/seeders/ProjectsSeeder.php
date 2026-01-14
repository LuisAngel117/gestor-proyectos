<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        if ($teams->count() === 0) {
            $this->command->warn('⚠️  No hay equipos disponibles. Ejecuta TeamsSeeder primero.');
            return;
        }

        // Proyecto 1: Sistema de Gestión Universitaria (Equipo 1)
        $team1 = $teams->where('name', 'Equipo Desarrollo Web')->first() ?? $teams->first();
        $admin = User::where('email', 'admin@gestor.test')->first();

        $project1 = Project::create([
            'team_id' => $team1->id,
            'name' => 'Sistema de Gestión Universitaria',
            'slug' => 'sistema-gestion-universitaria',
            'description' => 'Plataforma integral para la gestión académica, administrativa y estudiantil de la universidad.',
            'status' => 'en_progreso',
            'priority' => 'alta',
            'start_date' => now()->subMonths(2),
            'due_date' => now()->addMonths(4),
            'estimated_hours' => 320,
            'created_by' => $admin->id,
        ]);

        // Agregar miembros al proyecto 1
        $project1->addMember($admin, 'owner');
        $project1->addMember(User::where('email', 'maria@gestor.test')->first(), 'admin');
        $project1->addMember(User::where('email', 'juan@gestor.test')->first(), 'member');
        $project1->addMember(User::where('email', 'ana@gestor.test')->first(), 'member');

        // Proyecto 2: App Móvil de Reservas (Equipo 1)
        $project2 = Project::create([
            'team_id' => $team1->id,
            'name' => 'App Móvil de Reservas',
            'slug' => 'app-movil-reservas',
            'description' => 'Aplicación móvil para gestión de reservas de espacios y recursos universitarios.',
            'status' => 'planificacion',
            'priority' => 'media',
            'start_date' => now()->addWeeks(2),
            'due_date' => now()->addMonths(5),
            'estimated_hours' => 180,
            'created_by' => $admin->id,
        ]);

        $project2->addMember($admin, 'owner');
        $project2->addMember(User::where('email', 'carlos@gestor.test')->first(), 'admin');
        $project2->addMember(User::where('email', 'pedro@gestor.test')->first(), 'member');

        // Proyecto 3: Portal de Diseño UX (Equipo 2)
        $team2 = $teams->where('name', 'Equipo Diseño UX/UI')->first() ?? $teams->skip(1)->first();
        $carlos = User::where('email', 'carlos@gestor.test')->first();

        $project3 = Project::create([
            'team_id' => $team2->id,
            'name' => 'Portal de Diseño UX',
            'slug' => 'portal-diseno-ux',
            'description' => 'Rediseño completo de la experiencia de usuario del portal institucional.',
            'status' => 'en_progreso',
            'priority' => 'alta',
            'start_date' => now()->subMonth(),
            'due_date' => now()->addMonths(2),
            'estimated_hours' => 120,
            'created_by' => $carlos->id,
        ]);

        $project3->addMember($carlos, 'owner');
        $project3->addMember(User::where('email', 'ana@gestor.test')->first(), 'admin');
        $project3->addMember(User::where('email', 'pedro@gestor.test')->first(), 'member');
        $project3->addMember(User::where('email', 'laura@gestor.test')->first(), 'observer');

        // Proyecto 4: Sistema de Testing Automatizado (Equipo 3)
        $team3 = $teams->where('name', 'Equipo Testing y QA')->first() ?? $teams->skip(2)->first();
        $maria = User::where('email', 'maria@gestor.test')->first();

        $project4 = Project::create([
            'team_id' => $team3->id,
            'name' => 'Sistema de Testing Automatizado',
            'slug' => 'sistema-testing-automatizado',
            'description' => 'Implementación de suite completa de testing automatizado para todos los proyectos.',
            'status' => 'en_progreso',
            'priority' => 'urgente',
            'start_date' => now()->subWeeks(3),
            'due_date' => now()->addMonths(3),
            'estimated_hours' => 200,
            'created_by' => $maria->id,
        ]);

        $project4->addMember($maria, 'owner');
        $project4->addMember(User::where('email', 'diego@gestor.test')->first(), 'member');
        $project4->addMember(User::where('email', 'laura@gestor.test')->first(), 'member');

        // Proyecto 5: API Microservicios (Equipo 4)
        $team4 = $teams->where('name', 'Equipo Backend API')->first() ?? $teams->skip(3)->first();
        $juan = User::where('email', 'juan@gestor.test')->first();

        $project5 = Project::create([
            'team_id' => $team4->id,
            'name' => 'API Microservicios',
            'slug' => 'api-microservicios',
            'description' => 'Desarrollo de arquitectura de microservicios para los servicios backend principales.',
            'status' => 'planificacion',
            'priority' => 'alta',
            'start_date' => now()->addMonth(),
            'due_date' => now()->addMonths(6),
            'estimated_hours' => 400,
            'created_by' => $juan->id,
        ]);

        $project5->addMember($juan, 'owner');
        $project5->addMember(User::where('email', 'pedro@gestor.test')->first(), 'admin');
        $project5->addMember(User::where('email', 'diego@gestor.test')->first(), 'member');

        // Proyecto 6: Infraestructura CI/CD (Equipo 5)
        $team5 = $teams->where('name', 'Equipo DevOps')->first() ?? $teams->skip(4)->first();
        $pedro = User::where('email', 'pedro@gestor.test')->first();

        $project6 = Project::create([
            'team_id' => $team5->id,
            'name' => 'Infraestructura CI/CD',
            'slug' => 'infraestructura-cicd',
            'description' => 'Configuración de pipelines de integración continua y despliegue automático.',
            'status' => 'completado',
            'priority' => 'media',
            'start_date' => now()->subMonths(3),
            'due_date' => now()->subWeek(),
            'estimated_hours' => 150,
            'created_by' => $pedro->id,
        ]);

        $project6->addMember($pedro, 'owner');
        $project6->addMember($admin, 'admin');
        $project6->addMember(User::where('email', 'diego@gestor.test')->first(), 'member');

        $this->command->info('✅ 6 proyectos creados exitosamente con sus miembros');
    }
}
