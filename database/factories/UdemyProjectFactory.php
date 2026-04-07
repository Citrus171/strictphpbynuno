<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UdemyProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UdemyProject>
 */
final class UdemyProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
