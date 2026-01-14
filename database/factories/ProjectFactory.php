<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $projectTypes = [
            'Sistema de Gestión',
            'Plataforma Web',
            'Aplicación Móvil',
            'API REST',
            'Dashboard Analytics',
            'E-commerce',
            'CRM',
            'ERP',
            'Portal Corporativo',
            'Sistema de Inventario',
            'Plataforma Educativa',
            'Red Social',
            'Sistema de Reservas',
            'App de Delivery',
            'Sistema de Ticketing',
        ];

        $adjectives = [
            'Innovador',
            'Inteligente',
            'Moderno',
            'Eficiente',
            'Avanzado',
            'Premium',
            'Pro',
            'Enterprise',
            'Cloud',
            'Next-Gen',
        ];

        $name = fake()->randomElement($adjectives) . ' ' . fake()->randomElement($projectTypes);

        $startDate = fake()->optional(0.8)->dateTimeBetween('-6 months', 'now');
        $dueDate = $startDate ? fake()->optional(0.9)->dateTimeBetween($startDate, '+6 months') : null;

        return [
            'team_id' => Team::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional(0.8)->realText(300),
            'status' => fake()->randomElement(['planificacion', 'en_progreso', 'en_espera', 'completado', 'cancelado']),
            'priority' => fake()->randomElement(['baja', 'media', 'alta', 'urgente']),
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'estimated_hours' => fake()->optional(0.7)->numberBetween(40, 500),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the project is in planning status.
     */
    public function planning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planificacion',
        ]);
    }

    /**
     * Indicate that the project is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'en_progreso',
        ]);
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completado',
        ]);
    }

    /**
     * Indicate that the project has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'alta',
        ]);
    }

    /**
     * Indicate that the project has urgent priority.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgente',
        ]);
    }
}
