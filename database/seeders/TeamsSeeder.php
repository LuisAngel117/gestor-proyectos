<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios existentes
        $users = User::all();

        if ($users->count() === 0) {
            $this->command->warn('⚠️  No hay usuarios disponibles. Ejecuta UsersSeeder primero.');
            return;
        }

        // Equipo 1: Desarrollo Web (Owner: Admin Sistema)
        $team1 = Team::create([
            'name' => 'Equipo Desarrollo Web',
            'description' => 'Equipo dedicado al desarrollo de aplicaciones web con tecnologías modernas.',
            'owner_id' => $users->where('email', 'admin@gestor.test')->first()->id,
        ]);

        // Agregar miembros al equipo 1
        $team1->addMember($users->where('email', 'admin@gestor.test')->first(), 'owner');
        $team1->addMember($users->where('email', 'carlos@gestor.test')->first(), 'admin');
        $team1->addMember($users->where('email', 'maria@gestor.test')->first(), 'member');
        $team1->addMember($users->where('email', 'juan@gestor.test')->first(), 'member');
        $team1->addMember($users->where('email', 'ana@gestor.test')->first(), 'member');

        // Equipo 2: Diseño UX/UI (Owner: Carlos Administrador)
        $team2 = Team::create([
            'name' => 'Equipo Diseño UX/UI',
            'description' => 'Equipo especializado en diseño de experiencias de usuario e interfaces.',
            'owner_id' => $users->where('email', 'carlos@gestor.test')->first()->id,
        ]);

        // Agregar miembros al equipo 2
        $team2->addMember($users->where('email', 'carlos@gestor.test')->first(), 'owner');
        $team2->addMember($users->where('email', 'ana@gestor.test')->first(), 'admin');
        $team2->addMember($users->where('email', 'pedro@gestor.test')->first(), 'member');
        $team2->addMember($users->where('email', 'laura@gestor.test')->first(), 'observer');

        // Equipo 3: Testing y QA (Owner: María García)
        $team3 = Team::create([
            'name' => 'Equipo Testing y QA',
            'description' => 'Equipo responsable del aseguramiento de calidad y testing de software.',
            'owner_id' => $users->where('email', 'maria@gestor.test')->first()->id,
        ]);

        // Agregar miembros al equipo 3
        $team3->addMember($users->where('email', 'maria@gestor.test')->first(), 'owner');
        $team3->addMember($users->where('email', 'diego@gestor.test')->first(), 'member');
        $team3->addMember($users->where('email', 'laura@gestor.test')->first(), 'member');
        $team3->addMember($users->where('email', 'sofia@gestor.test')->first(), 'observer');

        // Equipo 4: Backend API (Owner: Juan Pérez)
        $team4 = Team::create([
            'name' => 'Equipo Backend API',
            'description' => 'Equipo enfocado en desarrollo de APIs RESTful y microservicios.',
            'owner_id' => $users->where('email', 'juan@gestor.test')->first()->id,
        ]);

        // Agregar miembros al equipo 4
        $team4->addMember($users->where('email', 'juan@gestor.test')->first(), 'owner');
        $team4->addMember($users->where('email', 'pedro@gestor.test')->first(), 'admin');
        $team4->addMember($users->where('email', 'diego@gestor.test')->first(), 'member');

        // Equipo 5: DevOps (Owner: Pedro Martínez)
        $team5 = Team::create([
            'name' => 'Equipo DevOps',
            'description' => 'Equipo de infraestructura, CI/CD y automatización.',
            'owner_id' => $users->where('email', 'pedro@gestor.test')->first()->id,
        ]);

        // Agregar miembros al equipo 5
        $team5->addMember($users->where('email', 'pedro@gestor.test')->first(), 'owner');
        $team5->addMember($users->where('email', 'admin@gestor.test')->first(), 'admin');
        $team5->addMember($users->where('email', 'diego@gestor.test')->first(), 'member');

        $this->command->info('✅ 5 equipos creados exitosamente con sus miembros');
    }
}
