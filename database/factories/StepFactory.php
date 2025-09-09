<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\Step;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Step>
 */
class StepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(array_keys(Step::TYPES));
        $timeType = $this->faker->randomElement(array_keys(Step::TIME_TYPES));
        $typeData = $this->generateTypeSpecificData($type);

        return [
            'program_id' => Program::factory(),
            'name' => $this->faker->sentence(3),
            'type' => $type,
            'time_to_finish' => $this->faker->numberBetween(1, 10),
            'time_type' => $timeType,
            // Individual columns
            'header' => $typeData['header'] ?? null,
            'content' => $typeData['content'] ?? null,
            'description' => $typeData['description'] ?? null,
            'content_url' => $typeData['content_url'] ?? null,
            'content_image' => $typeData['content_image'] ?? $typeData['cover_image'] ?? null,
            'advices' => $typeData['advices'] ?? [],
            'challenges' => $typeData['challenges'] ?? [],
        ];
    }

    /**
     * Generate type-specific data based on step type
     */
    private function generateTypeSpecificData(string $type): array
    {
        return match ($type) {
            'journal' => [
                'header' => $this->faker->sentence(),
            ],
            'article' => [
                'content' => $this->faker->paragraphs(3, true),
            ],
            'advice' => [
                'header' => $this->faker->sentence(),
                'advices' => $this->faker->sentences(rand(3, 7)),
            ],
            'daily_mission' => [
                'header' => $this->faker->sentence(),
                'content' => $this->faker->paragraph(),
            ],
            'quiz' => [
                'questions' => $this->faker->randomElements(range(1, 20), rand(5, 15)),
            ],
            'video' => [
                'content_url' => $this->faker->url(),
                'description' => $this->faker->paragraph(),
            ],
            'audio' => [
                'content_url' => $this->faker->url(),
                'description' => $this->faker->paragraph(),
            ],
            'book' => [
                'content_url' => $this->faker->url(),
                'cover_image' => $this->faker->imageUrl(400, 600, 'books'),
                'description' => $this->faker->paragraph(),
            ],
            'challenge' => [
                'description' => $this->faker->paragraph(),
                'challenges' => $this->faker->sentences(rand(3, 7)),
            ],
            default => [],
        };
    }

    /**
     * Create a journal step
     */
    public function journal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'journal',
            'name' => 'Journal: '.$this->faker->sentence(2),
            'header' => $this->faker->sentence(),
        ]);
    }

    /**
     * Create an article step
     */
    public function article(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'article',
            'name' => 'Article: '.$this->faker->sentence(2),
            'header' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(5, true),
        ]);
    }

    /**
     * Create an advice step
     */
    public function advice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'advice',
            'name' => 'Advice: '.$this->faker->sentence(2),
            'header' => $this->faker->sentence(),
            'advices' => $this->faker->sentences(rand(5, 10)),
        ]);
    }

    /**
     * Create a daily mission step
     */
    public function dailyMission(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'daily_mission',
            'name' => 'Daily Mission: '.$this->faker->sentence(2),
            'time_to_finish' => 1,
            'time_type' => 'days',
            'header' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a quiz step
     */
    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'quiz',
            'name' => 'Quiz: '.$this->faker->sentence(2),
            'time_to_finish' => $this->faker->numberBetween(30, 120),
            'time_type' => 'minutes',
            // Quiz questions will be managed separately
        ]);
    }

    /**
     * Create a video step
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'name' => 'Video: '.$this->faker->sentence(2),
            'time_to_finish' => $this->faker->numberBetween(10, 180),
            'time_type' => 'minutes',
            'content_url' => 'https://www.youtube.com/watch?v='.$this->faker->lexify('???????????'),
            'description' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create an audio step
     */
    public function audio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'audio',
            'name' => 'Audio: '.$this->faker->sentence(2),
            'time_to_finish' => $this->faker->numberBetween(15, 120),
            'time_type' => 'minutes',
            'content_url' => 'https://soundcloud.com/'.$this->faker->lexify('????????'),
            'description' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a book step
     */
    public function book(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'book',
            'name' => 'Book: '.$this->faker->sentence(2),
            'time_to_finish' => $this->faker->numberBetween(1, 4),
            'time_type' => 'weeks',
            'content_url' => $this->faker->url(),
            'content_image' => $this->faker->imageUrl(400, 600, 'books'),
            'description' => $this->faker->paragraphs(3, true),
        ]);
    }

    /**
     * Create a challenge step
     */
    public function challenge(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'challenge',
            'name' => 'Challenge: '.$this->faker->sentence(2),
            'time_to_finish' => $this->faker->numberBetween(1, 30),
            'time_type' => 'days',
            'header' => $this->faker->sentence(),
            'challenges' => $this->faker->sentences(rand(5, 15)),
        ]);
    }
}
