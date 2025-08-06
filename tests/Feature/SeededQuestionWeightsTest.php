<?php

namespace Tests\Feature;

use App\Http\Repositories\MethodologyRepository;
use App\Models\Methodology;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeededQuestionWeightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_data_includes_question_weights()
    {
        // Run the seeders
        $this->artisan('db:seed');
        
        // Get the first methodology
        $methodology = Methodology::first();
        $this->assertNotNull($methodology);
        
        // Test the repository
        $methodologyRepo = new MethodologyRepository();
        $methodologyWithWeights = $methodologyRepo->findByIdWithFullDetails($methodology->id);
        
        // Verify methodology questions have weights
        foreach ($methodologyWithWeights->questions as $question) {
            $this->assertTrue(isset($question->pivot->weight));
            $this->assertEquals(1.0, $question->pivot->weight);
        }
        
        // Verify pillar questions have weights
        foreach ($methodologyWithWeights->pillars as $pillar) {
            foreach ($pillar->questions as $question) {
                $this->assertTrue(isset($question->pivot->weight));
                $this->assertEquals(1.0, $question->pivot->weight);
            }
            
            // Verify module questions have weights
            foreach ($pillar->modules as $module) {
                foreach ($module->questions as $question) {
                    $this->assertTrue(isset($question->pivot->weight));
                    $this->assertEquals(1.0, $question->pivot->weight);
                }
            }
        }
        
        // Verify direct module questions have weights
        foreach ($methodologyWithWeights->modules as $module) {
            foreach ($module->questions as $question) {
                $this->assertTrue(isset($question->pivot->weight));
                $this->assertEquals(1.0, $question->pivot->weight);
            }
        }
    }
} 