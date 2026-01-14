<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cargos = [
            'Desarrollador Frontend',
            'Desarrollador Backend',
            'Desarrollador Full Stack',
            'Diseñador UX/UI',
            'Gestor de Proyectos',
            'Analista de Sistemas',
            'Tester QA',
            'DevOps Engineer',
            'Scrum Master',
            'Product Owner',
        ];

        $departamentos = [
            'Ingeniería de Software',
            'Ciencias de la Computación',
            'Sistemas de Información',
            'Tecnología',
            'Investigación y Desarrollo',
            'Administración de Proyectos',
        ];

        return [
            'user_id' => User::factory(),
            'cargo' => fake()->randomElement($cargos),
            'departamento' => fake()->randomElement($departamentos),
            'id_universitario' => fake()->unique()->numerify('EST-####-####'),
            'telefono' => fake()->optional(0.8)->numerify('+593-9########'),
            'bio' => fake()->optional(0.7)->realText(200),
        ];
    }
}
