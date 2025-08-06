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

class ModuleInPillarQuestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_questions_for_pillar_in_methodology()
    {
        // Create a methodology
        $methodology = Methodology::create([
            'name' => 'Test Methodology',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'complex',
        ]);

        // Create a pillar
        $pillar = Pillar::create(['name' => 'Test Pillar', 'description' => 'Test', 'definition' => 'Test']);

        // Create a module
        $module = Module::create(['name' => 'Test Module', 'description' => 'Test', 'definition' => 'Test']);

        // Create a question
        $question = Question::create(['title' => 'Module in Pillar Question', 'type' => 'MCQMultiple']);

        // Create answers
        $optionA = Answer::create(['title' => 'Option A']);
        $optionB = Answer::create(['title' => 'Option B']);

        // Attach answers to question
        $question->answers()->attach([$optionA->id, $optionB->id]);

        // Attach relationships
        $methodology->pillars()->attach($pillar->id, ['section' => 'first']);
        $pillar->modules()->attach($module->id);

        // Insert module question with both methodology and pillar context
        \DB::table('module_question')->insert([
            'methodology_id' => $methodology->id,
            'module_id' => $module->id,
            'pillar_id' => $pillar->id,
            'question_id' => $question->id,
            'weight' => 2.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the pivot ID
        $pivotId = \DB::table('module_question')
            ->where('methodology_id', $methodology->id)
            ->where('module_id', $module->id)
            ->where('pillar_id', $pillar->id)
            ->where('question_id', $question->id)
            ->value('id');

        // Create weights for answers
        QuestionAnswerWeight::create([
            'context_type' => 'module_question',
            'context_id' => $pivotId,
            'answer_id' => $optionA->id,
            'weight' => 3.0,
        ]);

        QuestionAnswerWeight::create([
            'context_type' => 'module_question',
            'context_id' => $pivotId,
            'answer_id' => $optionB->id,
            'weight' => 1.0,
        ]);

        // Test the repository
        $questionRepo = new QuestionRepository();

        // Get questions for module in pillar within methodology
        $questions = $questionRepo->getQuestionsByContext('module', $module->id, $methodology->id, $pillar->id);
        $this->assertCount(1, $questions);
        $this->assertEquals('Module in Pillar Question', $questions->first()->title);

        // Test the API endpoint
        $controller = new QuestionController($questionRepo);

        $response = $controller->getModuleQuestionsForPillarInMethodology($methodology->id, $pillar->id, $module->id);
        
        // Verify response structure
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        
        // Verify we have questions
        $this->assertNotEmpty($data);
        
        // Verify the first question has answers with weights
        $firstQuestion = $data[0];
        $this->assertEquals('Module in Pillar Question', $firstQuestion['title']);
        $this->assertEquals('multiple', $firstQuestion['type']);
        $this->assertEquals(2.0, $firstQuestion['weight']); // Question weight
        
        // Verify answers have weights
        $this->assertCount(2, $firstQuestion['answers']);
        
        $optionAData = collect($firstQuestion['answers'])->firstWhere('title', 'Option A');
        $optionBData = collect($firstQuestion['answers'])->firstWhere('title', 'Option B');
        
        $this->assertNotNull($optionAData);
        $this->assertNotNull($optionBData);
        $this->assertEquals(3.0, $optionAData['weight']);
        $this->assertEquals(1.0, $optionBData['weight']);
    }

    public function test_module_questions_different_in_different_pillars()
    {
        // Create a methodology
        $methodology = Methodology::create([
            'name' => 'Test Methodology',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'complex',
        ]);

        // Create two pillars
        $pillar1 = Pillar::create(['name' => 'Pillar 1', 'description' => 'Test', 'definition' => 'Test']);
        $pillar2 = Pillar::create(['name' => 'Pillar 2', 'description' => 'Test', 'definition' => 'Test']);

        // Create a shared module
        $module = Module::create(['name' => 'Shared Module', 'description' => 'Test', 'definition' => 'Test']);

        // Create different questions for the same module in different pillars
        $question1 = Question::create(['title' => 'Question for Pillar 1', 'type' => 'YesNo']);
        $question2 = Question::create(['title' => 'Question for Pillar 2', 'type' => 'TrueFalse']);

        // Create answers
        $yesAnswer = Answer::create(['title' => 'Yes']);
        $noAnswer = Answer::create(['title' => 'No']);
        $trueAnswer = Answer::create(['title' => 'True']);
        $falseAnswer = Answer::create(['title' => 'False']);

        // Attach answers to questions
        $question1->answers()->attach([$yesAnswer->id, $noAnswer->id]);
        $question2->answers()->attach([$trueAnswer->id, $falseAnswer->id]);

        // Attach pillars to methodology
        $methodology->pillars()->attach($pillar1->id, ['section' => 'first']);
        $methodology->pillars()->attach($pillar2->id, ['section' => 'second']);

        // Attach module to both pillars
        $pillar1->modules()->attach($module->id);
        $pillar2->modules()->attach($module->id);

        // Insert module questions with different pillar contexts
        \DB::table('module_question')->insert([
            'methodology_id' => $methodology->id,
            'module_id' => $module->id,
            'pillar_id' => $pillar1->id,
            'question_id' => $question1->id,
            'weight' => 1.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('module_question')->insert([
            'methodology_id' => $methodology->id,
            'module_id' => $module->id,
            'pillar_id' => $pillar2->id,
            'question_id' => $question2->id,
            'weight' => 2.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test the repository
        $questionRepo = new QuestionRepository();

        // Get questions for module in pillar 1
        $questions1 = $questionRepo->getQuestionsByContext('module', $module->id, $methodology->id, $pillar1->id);
        $this->assertCount(1, $questions1);
        $this->assertEquals('Question for Pillar 1', $questions1->first()->title);

        // Get questions for module in pillar 2
        $questions2 = $questionRepo->getQuestionsByContext('module', $module->id, $methodology->id, $pillar2->id);
        $this->assertCount(1, $questions2);
        $this->assertEquals('Question for Pillar 2', $questions2->first()->title);

        // Test the API endpoints
        $controller = new QuestionController($questionRepo);

        // Test module questions for pillar 1
        $response1 = $controller->getModuleQuestionsForPillarInMethodology($methodology->id, $pillar1->id, $module->id);
        $this->assertEquals(200, $response1->getStatusCode());
        $data1 = json_decode($response1->getContent(), true);
        $this->assertCount(1, $data1);
        $this->assertEquals('Question for Pillar 1', $data1[0]['title']);

        // Test module questions for pillar 2
        $response2 = $controller->getModuleQuestionsForPillarInMethodology($methodology->id, $pillar2->id, $module->id);
        $this->assertEquals(200, $response2->getStatusCode());
        $data2 = json_decode($response2->getContent(), true);
        $this->assertCount(1, $data2);
        $this->assertEquals('Question for Pillar 2', $data2[0]['title']);
    }
} 