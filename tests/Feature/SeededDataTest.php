<?php

namespace Tests\Feature;

use App\Http\Repositories\QuestionRepository;
use App\Models\Methodology;
use App\Models\QuestionAnswerWeight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeededDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_data_has_weights()
    {
        // Run the seeders
        $this->artisan('db:seed');
        
        // Get the first methodology
        $methodology = Methodology::first();
        $this->assertNotNull($methodology);
        
        // Test the repository
        $questionRepo = new QuestionRepository();
        $questions = $questionRepo->getQuestionsByContext('methodology', $methodology->id);
        
        // Verify we have questions
        $this->assertNotEmpty($questions);
        
        // Verify each question has weights
        foreach ($questions as $question) {
            $this->assertTrue(isset($question->answer_weights));
            $this->assertGreaterThan(0, $question->answer_weights->count());
            
            // Verify each answer has a weight
            foreach ($question->answers as $answer) {
                $this->assertTrue($question->answer_weights->has($answer->id));
                $this->assertEquals(1.0, $question->answer_weights->get($answer->id)->weight);
            }
        }
        
        // Verify we have weight records in the database
        $weightCount = QuestionAnswerWeight::count();
        $this->assertGreaterThan(0, $weightCount);
    }
} 