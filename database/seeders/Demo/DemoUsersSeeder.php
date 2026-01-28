<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'apellido' => 'Sistema',
                'email' => 'admin@gestor.test',
                'role' => 'superadmin',
                'estado' => 'activo',
            ],
            [
                'name' => 'Carlos',
                'apellido' => 'Administrador',
                'email' => 'carlos@gestor.test',
                'role' => 'user',
                'estado' => 'activo',
            ],
            [
                'name' => 'Maria',
                'apellido' => 'Garcia',
                'email' => 'maria@gestor.test',
                'role' => 'user',
                'estado' => 'activo',
            ],
            [
                'name' => 'Juan',
                'apellido' => 'Perez',
                'email' => 'juan@gestor.test',
                'role' => 'user',
                'estado' => 'activo',
            ],
            [
                'name' => 'Ana',
                'apellido' => 'Rodriguez',
                'email' => 'ana@gestor.test',
                'role' => 'user',
                'estado' => 'activo',
            ],
            [
                'name' => 'Pedro',
                'apellido' => 'Martinez',
                'email' => 'pedro@gestor.test',
                'role' => 'user',
                'estado' => 'activo',
            ],
            [
                'name' => 'Laura',
                'apellido' => 'Sanchez',
                'email' => 'laura@gestor.test',
                'role' => 'user',
                'estado' => 'activo',
            ],
            [
                'name' => 'Diego',
                'apellido' => 'Lopez',
                'email' => 'diego@gestor.test',
                'role' => 'user',
                'estado' => 'activo',
            ],
            [
                'name' => 'Sofia',
                'apellido' => 'Ramirez',
                'email' => 'sofia@gestor.test',
                'role' => 'user',
                'estado' => 'activo',
            ],
            [
                'name' => 'Miguel',
                'apellido' => 'Torres',
                'email' => 'miguel@gestor.test',
                'role' => 'user',
                'estado' => 'activo',
            ],
        ];

        foreach ($users as $payload) {
            User::updateOrCreate(
                ['email' => $payload['email']],
                array_merge($payload, [
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'last_login_at' => now(),
                    'must_change_password' => false,
                    'profile_completed_at' => now(),
                ])
            );
        }

        $this->command->info('Demo: 10 usuarios listos.');
    }
}
