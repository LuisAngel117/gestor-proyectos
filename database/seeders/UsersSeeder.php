<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Superadmin
        User::create([
            'name' => 'Admin',
            'apellido' => 'Sistema',
            'email' => 'admin@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'estado' => 'activo',
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ]);

        // Usuarios normales
        User::create([
            'name' => 'Carlos',
            'apellido' => 'Administrador',
            'email' => 'carlos@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'estado' => 'activo',
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ]);

        User::create([
            'name' => 'María',
            'apellido' => 'García',
            'email' => 'maria@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Ana',
            'apellido' => 'Rodríguez',
            'email' => 'ana@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Pedro',
            'apellido' => 'Martínez',
            'email' => 'pedro@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Laura',
            'apellido' => 'Sánchez',
            'email' => 'laura@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Diego',
            'apellido' => 'López',
            'email' => 'diego@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Sofia',
            'apellido' => 'Ramírez',
            'email' => 'sofia@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'estado' => 'inactivo',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Miguel',
            'apellido' => 'Torres',
            'email' => 'miguel@gestor.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ 10 usuarios creados exitosamente');
    }
}
