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
        $type = $this->faker->randomElement(['simple', 'complex', 'twoSection']);

        $firstSection = [
            'first_section_name' => $this->faker->words(2, true),
            'first_section_description' => $this->faker->optional()->paragraph(),
            'first_section_definition' => $this->faker->optional()->sentence(),
            'first_section_objectives' => $this->faker->optional()->paragraphs(2, true),
            'first_section_img_url' => $this->faker->optional()->imageUrl(),
        ];

        $secondSection = $type === 'twoSection' ? [
            'second_section_name' => $this->faker->words(2, true),
            'second_section_description' => $this->faker->optional()->paragraph(),
            'second_section_definition' => $this->faker->optional()->sentence(),
            'second_section_objectives' => $this->faker->optional()->paragraphs(2, true),
            'second_section_img_url' => $this->faker->optional()->imageUrl(),
        ] : [
            'second_section_name' => null,
            'second_section_description' => null,
            'second_section_definition' => null,
            'second_section_objectives' => null,
            'second_section_img_url' => null,
        ];

        return array_merge([
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'definition' => $this->faker->sentence(),
            'objectives' => $this->faker->optional()->paragraphs(3, true),
            'img_url' => $this->faker->optional()->imageUrl(),
            'type' => $type,
            'pillars_definition' => $this->faker->optional()->sentence(),
            'modules_definition' => $this->faker->optional()->sentence(),
            'questions_description' => $this->faker->optional()->paragraph(),
            'questions_estimated_time' => $this->faker->optional()->randomElement(['15-20 minutes','45-60 minutes','60-90 minutes']),
            'questions_count' => 0,
            'tags' => $this->faker->optional()->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 3)),
        ], $firstSection, $secondSection);
    }
} 