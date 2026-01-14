<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $teamTypes = [
            'Desarrollo',
            'Diseño',
            'Marketing',
            'Investigación',
            'Infraestructura',
            'QA',
            'DevOps',
            'Frontend',
            'Backend',
            'Full Stack',
        ];

        $projects = [
            'Sistema de Gestión',
            'Plataforma Web',
            'App Móvil',
            'API REST',
            'Dashboard Analytics',
            'E-commerce',
            'CRM',
            'ERP',
            'Portal',
            'Microservicios',
        ];

        return [
            'name' => fake()->randomElement($teamTypes) . ' - ' . fake()->randomElement($projects),
            'description' => fake()->realText(200),
            'owner_id' => User::factory(),
        ];
    }
}
