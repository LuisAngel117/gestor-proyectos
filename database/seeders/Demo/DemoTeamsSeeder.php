<?php

namespace Database\Seeders\Demo;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTeamsSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'admin@gestor.test')->first();
        if (!$owner) {
            $this->command->warn('Demo: usuarios no disponibles.');
            return;
        }

        $team = Team::updateOrCreate(
            ['name' => 'Equipo Demo'],
            [
                'description' => 'Equipo demo para QA local.',
                'owner_id' => $owner->id,
            ]
        );

        $roles = [
            'admin@gestor.test' => 'owner',
            'carlos@gestor.test' => 'admin',
            'maria@gestor.test' => 'member',
            'juan@gestor.test' => 'member',
            'ana@gestor.test' => 'member',
            'pedro@gestor.test' => 'member',
            'laura@gestor.test' => 'member',
            'diego@gestor.test' => 'member',
            'sofia@gestor.test' => 'observer',
            'miguel@gestor.test' => 'observer',
        ];

        foreach ($roles as $email => $role) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                continue;
            }

            $team->addMember($user, $role);
            $team->updateMemberRole($user, $role);
        }

        $this->command->info('Demo: equipo demo creado.');
    }
}
