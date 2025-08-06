<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionWeightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_questions_have_weights_in_pivot_tables()
    {
        // Create a methodology
        $methodology = Methodology::create([
            'name' => 'Test Methodology',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'complex',
        ]);

        // Create questions
        $methodologyQuestion = Question::create(['title' => 'Methodology Question', 'type' => 'YesNo']);
        $pillarQuestion = Question::create(['title' => 'Pillar Question', 'type' => 'TrueFalse']);
        $moduleQuestion = Question::create(['title' => 'Module Question', 'type' => 'MCQMultiple']);

        // Create pillar and module
        $pillar = Pillar::create(['name' => 'Test Pillar', 'description' => 'Test', 'definition' => 'Test']);
        $module = Module::create(['name' => 'Test Module', 'description' => 'Test', 'definition' => 'Test']);

        // Attach questions with weights
        $methodology->questions()->attach($methodologyQuestion->id, ['weight' => 2.5]);
        $methodology->pillars()->attach($pillar->id, ['section' => 'first']);
        
        // Insert pillar question with methodology context
        \DB::table('pillar_question')->insert([
            'methodology_id' => $methodology->id,
            'pillar_id' => $pillar->id,
            'question_id' => $pillarQuestion->id,
            'weight' => 3.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $pillar->modules()->attach($module->id);
        
        // Insert module question with methodology context
        \DB::table('module_question')->insert([
            'methodology_id' => $methodology->id,
            'module_id' => $module->id,
            'pillar_id' => null,
            'question_id' => $moduleQuestion->id,
            'weight' => 1.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test methodology questions have weights
        $methodologyWithQuestions = Methodology::with('questions')->find($methodology->id);
        $firstMethodologyQuestion = $methodologyWithQuestions->questions->first();
        $this->assertEquals(2.5, $firstMethodologyQuestion->pivot->weight);

        // Test pillar questions have weights
        $pillarWithQuestions = Pillar::with('questions')->find($pillar->id);
        $firstPillarQuestion = $pillarWithQuestions->questions->first();
        $this->assertEquals(3.0, $firstPillarQuestion->pivot->weight);

        // Test module questions have weights
        $moduleWithQuestions = Module::with('questions')->find($module->id);
        $firstModuleQuestion = $moduleWithQuestions->questions->first();
        $this->assertEquals(1.5, $firstModuleQuestion->pivot->weight);
    }

    public function test_question_resource_includes_weight()
    {
        // Create a methodology and question
        $methodology = Methodology::create([
            'name' => 'Test Methodology',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'simple',
        ]);

        $question = Question::create(['title' => 'Test Question', 'type' => 'YesNo']);

        // Attach question with weight
        $methodology->questions()->attach($question->id, ['weight' => 2.5]);

        // Load the relationship
        $methodologyWithQuestions = Methodology::with('questions')->find($methodology->id);
        $questionWithPivot = $methodologyWithQuestions->questions->first();

        // Test the resource
        $resource = new \App\Resources\QuestionResource($questionWithPivot);
        $data = $resource->toArray(request());

        $this->assertArrayHasKey('weight', $data);
        $this->assertEquals(2.5, $data['weight']);
    }
} 