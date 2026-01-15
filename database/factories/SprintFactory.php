<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Sprint>
 */
class SprintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-3 months', '+1 month');
        $endDate = (clone $startDate)->modify('+14 days');

        return [
            'project_id' => Project::factory(),
            'name' => 'Sprint ' . fake()->numberBetween(1, 30),
            'goal' => fake()->sentence(),
            'sequence' => fake()->unique()->numberBetween(1, 30),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => fake()->randomElement(['planificacion', 'activo', 'cerrado']),
            'created_by' => User::factory(),
        ];
    }
}
