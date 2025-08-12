<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['YesNo', 'TrueFalse', 'MCQSingle', 'MCQMultiple', 'Rating1to5', 'Rating1to10', 'ScaleAgreeDisagree']),
            'tags' => $this->faker->optional()->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 3)),
            'active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }
} 