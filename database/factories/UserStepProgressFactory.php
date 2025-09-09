<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\Step;
use App\Models\User;
use App\Models\UserStepProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserStepProgress>
 */
class UserStepProgressFactory extends Factory
{
    protected $model = UserStepProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['not_started', 'in_progress', 'completed', 'skipped']);

        return [
            'user_id' => User::factory(),
            'program_id' => Program::factory(),
            'step_id' => Step::factory(),
            'status' => $status,
            'thought' => $status === 'completed' ? fake()->optional()->paragraph() : null,
            'score' => $status === 'completed' ? fake()->optional()->numberBetween(0, 100) : null,
            'challenges_done' => $status !== 'not_started' ? fake()->numberBetween(0, 10) : 0,
            'percentage' => $this->getPercentageForStatus($status),
            'started_at' => $status !== 'not_started' ? fake()->dateTimeBetween('-30 days', '-1 day') : null,
            'completed_at' => in_array($status, ['completed', 'skipped']) ? fake()->dateTimeBetween('-10 days', 'now') : null,
        ];
    }

    /**
     * Indicate that the progress is not started.
     */
    public function notStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not_started',
            'thought' => null,
            'score' => null,
            'challenges_done' => 0,
            'percentage' => 0.00,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the progress is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'challenges_done' => fake()->numberBetween(1, 8),
            'percentage' => fake()->randomFloat(2, 10.00, 80.00),
            'started_at' => fake()->dateTimeBetween('-10 days', '-1 day'),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the progress is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'thought' => fake()->optional(0.7)->paragraph(),
            'score' => fake()->optional(0.5)->numberBetween(60, 100),
            'challenges_done' => fake()->numberBetween(5, 10),
            'percentage' => 100.00,
            'started_at' => fake()->dateTimeBetween('-15 days', '-5 days'),
            'completed_at' => fake()->dateTimeBetween('-5 days', 'now'),
        ]);
    }

    /**
     * Indicate that the progress is skipped.
     */
    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'skipped',
            'thought' => null,
            'score' => null,
            'challenges_done' => 0,
            'percentage' => 0.00,
            'started_at' => null,
            'completed_at' => fake()->dateTimeBetween('-5 days', 'now'),
        ]);
    }

    /**
     * Get appropriate percentage for given status.
     */
    private function getPercentageForStatus(string $status): float
    {
        return match ($status) {
            'not_started' => 0.00,
            'in_progress' => fake()->randomFloat(2, 10.00, 80.00),
            'completed' => 100.00,
            'skipped' => 0.00,
        };
    }
}
