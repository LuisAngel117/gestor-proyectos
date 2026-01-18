<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $profilesData = [
            [
                'cargo' => 'Administrador de Sistema',
                'departamento' => 'Tecnología',
                'id_universitario' => 'ADM-0001-2024',
                'telefono' => '+593-999888777',
                'bio' => 'Administrador principal del sistema de gestión de proyectos.',
            ],
            [
                'cargo' => 'Gestor de Proyectos',
                'departamento' => 'Administración de Proyectos',
                'id_universitario' => 'ADM-0002-2024',
                'telefono' => '+593-999888766',
                'bio' => 'Responsable de la coordinación y seguimiento de proyectos.',
            ],
            [
                'cargo' => 'Desarrolladora Frontend',
                'departamento' => 'Ingeniería de Software',
                'id_universitario' => 'EST-2024-0001',
                'telefono' => '+593-987654321',
                'bio' => 'Especializada en React y Vue.js, con experiencia en interfaces de usuario.',
            ],
            [
                'cargo' => 'Desarrollador Backend',
                'departamento' => 'Ingeniería de Software',
                'id_universitario' => 'EST-2024-0002',
                'telefono' => '+593-987654322',
                'bio' => 'Experto en Laravel y API REST, enfocado en arquitectura de sistemas.',
            ],
            [
                'cargo' => 'Diseñadora UX/UI',
                'departamento' => 'Diseño',
                'id_universitario' => 'EST-2024-0003',
                'telefono' => '+593-987654323',
                'bio' => 'Apasionada por crear experiencias de usuario intuitivas y atractivas.',
            ],
            [
                'cargo' => 'Analista de Sistemas',
                'departamento' => 'Sistemas de Información',
                'id_universitario' => 'EST-2024-0004',
                'telefono' => '+593-987654324',
                'bio' => 'Análisis de requerimientos y diseño de soluciones tecnológicas.',
            ],
            [
                'cargo' => 'Observadora de Calidad',
                'departamento' => 'QA',
                'id_universitario' => 'EST-2024-0005',
                'telefono' => '+593-987654325',
                'bio' => 'Encargada del control de calidad y revisión de procesos.',
            ],
            [
                'cargo' => 'Desarrollador Full Stack',
                'departamento' => 'Ingeniería de Software',
                'id_universitario' => 'EST-2024-0006',
                'telefono' => '+593-987654326',
                'bio' => 'Desarrollo en frontend y backend, con enfoque en aplicaciones web.',
            ],
            [
                'cargo' => 'Tester QA',
                'departamento' => 'Testing',
                'id_universitario' => 'EST-2024-0007',
                'telefono' => null,
                'bio' => null,
            ],
            [
                'cargo' => 'Invitado Externo',
                'departamento' => 'Externo',
                'id_universitario' => 'EXT-2024-0001',
                'telefono' => null,
                'bio' => 'Usuario externo con acceso limitado.',
            ],
        ];

        foreach ($users as $index => $user) {
            if (!isset($profilesData[$index])) {
                continue;
            }

            Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'cargo' => $profilesData[$index]['cargo'],
                    'departamento' => $profilesData[$index]['departamento'],
                    'id_universitario' => $profilesData[$index]['id_universitario'],
                    'telefono' => $profilesData[$index]['telefono'],
                    'bio' => $profilesData[$index]['bio'],
                ]
            );
        }

        $this->command->info('✅ 10 perfiles creados exitosamente');
    }
}
