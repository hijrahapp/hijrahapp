<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
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
            'tags' => $this->faker->optional()->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 3)),
        ];
    }
} 