<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pillar>
 */
class PillarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->paragraph(),
            'definition' => $this->faker->sentence(),
            'objectives' => $this->faker->optional()->paragraphs(3, true),
            'img_url' => $this->faker->optional()->imageUrl(),
            'questions_description' => $this->faker->optional()->paragraph(),
            'questions_estimated_time' => $this->faker->optional()->randomElement(['10-15 minutes']),
            'questions_count' => 0,
            'tags' => $this->faker->optional()->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 3)),
        ];
    }
} 