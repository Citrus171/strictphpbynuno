<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UdemyProject;
use App\Models\UdemyTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UdemyTask>
 */
final class UdemyTaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => UdemyProject::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
