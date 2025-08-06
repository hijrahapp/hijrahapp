<?php

namespace Tests\Feature;

use App\Http\Controllers\QuestionController;
use App\Http\Repositories\QuestionRepository;
use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use App\Models\QuestionAnswerWeight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PillarModuleQuestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pillar_questions_endpoint_returns_weights()
    {
        // Create a methodology and pillar
        $methodology = Methodology::create([
            'name' => 'Test Methodology',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'complex',
        ]);

        $pillar = Pillar::create(['name' => 'Test Pillar', 'description' => 'Test', 'definition' => 'Test']);

        // Create a question
        $question = Question::create(['title' => 'Pillar Question', 'type' => 'TrueFalse']);

        // Create answers
        $trueAnswer = Answer::create(['title' => 'True']);
        $falseAnswer = Answer::create(['title' => 'False']);

        // Attach answers to question
        $question->answers()->attach([$trueAnswer->id, $falseAnswer->id]);

        // Attach pillar to methodology and question to pillar
        $methodology->pillars()->attach($pillar->id, ['section' => 'first']);
        
        // Insert pillar question with methodology context
        \DB::table('pillar_question')->insert([
            'methodology_id' => $methodology->id,
            'pillar_id' => $pillar->id,
            'question_id' => $question->id,
            'weight' => 2.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the pivot ID
        $pivotId = \DB::table('pillar_question')
            ->where('methodology_id', $methodology->id)
            ->where('pillar_id', $pillar->id)
            ->where('question_id', $question->id)
            ->value('id');

        // Create weights for answers
        QuestionAnswerWeight::create([
            'context_type' => 'pillar_question',
            'context_id' => $pivotId,
            'answer_id' => $trueAnswer->id,
            'weight' => 3.0,
        ]);

        QuestionAnswerWeight::create([
            'context_type' => 'pillar_question',
            'context_id' => $pivotId,
            'answer_id' => $falseAnswer->id,
            'weight' => 1.0,
        ]);

        // Test the controller directly
        $questionRepo = new QuestionRepository();
        $controller = new QuestionController($questionRepo);
        
        $response = $controller->getPillarQuestionsForMethodology($methodology->id, $pillar->id);
        
        // Verify response structure
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        
        // Verify we have questions
        $this->assertNotEmpty($data);
        
        // Verify the first question has answers with weights
        $firstQuestion = $data[0];
        $this->assertEquals('Pillar Question', $firstQuestion['title']);
        $this->assertEquals('single', $firstQuestion['type']);
        $this->assertEquals(2.5, $firstQuestion['weight']); // Question weight
        
        // Verify answers have weights
        $this->assertCount(2, $firstQuestion['answers']);
        
        $trueAnswerData = collect($firstQuestion['answers'])->firstWhere('title', 'True');
        $falseAnswerData = collect($firstQuestion['answers'])->firstWhere('title', 'False');
        
        $this->assertNotNull($trueAnswerData);
        $this->assertNotNull($falseAnswerData);
        $this->assertEquals(3.0, $trueAnswerData['weight']);
        $this->assertEquals(1.0, $falseAnswerData['weight']);
    }

    public function test_module_questions_endpoint_returns_weights()
    {
        // Create a methodology, pillar, and module
        $methodology = Methodology::create([
            'name' => 'Test Methodology',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'complex',
        ]);

        $pillar = Pillar::create(['name' => 'Test Pillar', 'description' => 'Test', 'definition' => 'Test']);
        $module = Module::create(['name' => 'Test Module', 'description' => 'Test', 'definition' => 'Test']);

        // Create a question
        $question = Question::create(['title' => 'Module Question', 'type' => 'MCQMultiple']);

        // Create answers
        $optionA = Answer::create(['title' => 'Option A']);
        $optionB = Answer::create(['title' => 'Option B']);

        // Attach answers to question
        $question->answers()->attach([$optionA->id, $optionB->id]);

        // Attach relationships
        $methodology->pillars()->attach($pillar->id, ['section' => 'first']);
        $pillar->modules()->attach($module->id);
        
        // Insert module question with methodology context
        \DB::table('module_question')->insert([
            'methodology_id' => $methodology->id,
            'module_id' => $module->id,
            'pillar_id' => $pillar->id,
            'question_id' => $question->id,
            'weight' => 1.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the pivot ID
        $pivotId = \DB::table('module_question')
            ->where('methodology_id', $methodology->id)
            ->where('module_id', $module->id)
            ->where('question_id', $question->id)
            ->value('id');

        // Create weights for answers
        QuestionAnswerWeight::create([
            'context_type' => 'module_question',
            'context_id' => $pivotId,
            'answer_id' => $optionA->id,
            'weight' => 2.0,
        ]);

        QuestionAnswerWeight::create([
            'context_type' => 'module_question',
            'context_id' => $pivotId,
            'answer_id' => $optionB->id,
            'weight' => 1.0,
        ]);

        // Test the controller directly
        $questionRepo = new QuestionRepository();
        $controller = new QuestionController($questionRepo);
        
        $response = $controller->getModuleQuestionsForMethodology($methodology->id, $module->id);
        
        // Verify response structure
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        
        // Verify we have questions
        $this->assertNotEmpty($data);
        
        // Verify the first question has answers with weights
        $firstQuestion = $data[0];
        $this->assertEquals('Module Question', $firstQuestion['title']);
        $this->assertEquals('multiple', $firstQuestion['type']);
        $this->assertEquals(1.5, $firstQuestion['weight']); // Question weight
        
        // Verify answers have weights
        $this->assertCount(2, $firstQuestion['answers']);
        
        $optionAData = collect($firstQuestion['answers'])->firstWhere('title', 'Option A');
        $optionBData = collect($firstQuestion['answers'])->firstWhere('title', 'Option B');
        
        $this->assertNotNull($optionAData);
        $this->assertNotNull($optionBData);
        $this->assertEquals(2.0, $optionAData['weight']);
        $this->assertEquals(1.0, $optionBData['weight']);
    }
} 