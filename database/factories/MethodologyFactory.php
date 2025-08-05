<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Methodology>
 */
class MethodologyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'definition' => $this->faker->sentence(),
            'objectives' => $this->faker->optional()->paragraphs(3, true),
            'type' => $this->faker->randomElement(['simple', 'complex', 'twoSection']),
            'first_section_name' => $this->faker->optional()->words(2, true),
            'second_section_name' => $this->faker->optional()->words(2, true),
            'pillars_definition' => $this->faker->optional()->sentence(),
            'modules_definition' => $this->faker->optional()->sentence(),
            'tags' => $this->faker->optional()->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 3)),
        ];
    }
} 