<?php

namespace Database\Factories;

use App\Models\BacklogItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BacklogItem>
 */
class BacklogItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional(0.6)->paragraph(),
            'priority' => fake()->randomElement(['baja', 'media', 'alta', 'urgente']),
            'status' => fake()->randomElement(['backlog', 'refinado']),
            'position' => fake()->numberBetween(1, 50),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate the backlog item is refined.
     */
    public function refined(): static
    {
        return $this->state(fn () => [
            'status' => 'refinado',
        ]);
    }
}
