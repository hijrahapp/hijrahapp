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

class MethodologyAwareQuestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pillar_questions_are_methodology_aware()
    {
        // Create two methodologies
        $methodology1 = Methodology::create([
            'name' => 'Methodology 1',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'complex',
        ]);

        $methodology2 = Methodology::create([
            'name' => 'Methodology 2',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'complex',
        ]);

        // Create a shared pillar
        $pillar = Pillar::create(['name' => 'Shared Pillar', 'description' => 'Test', 'definition' => 'Test']);

        // Create questions
        $question1 = Question::create(['title' => 'Question for Methodology 1', 'type' => 'YesNo']);
        $question2 = Question::create(['title' => 'Question for Methodology 2', 'type' => 'TrueFalse']);

        // Create answers
        $yesAnswer = Answer::create(['title' => 'Yes']);
        $noAnswer = Answer::create(['title' => 'No']);
        $trueAnswer = Answer::create(['title' => 'True']);
        $falseAnswer = Answer::create(['title' => 'False']);

        // Attach answers to questions
        $question1->answers()->attach([$yesAnswer->id, $noAnswer->id]);
        $question2->answers()->attach([$trueAnswer->id, $falseAnswer->id]);

        // Attach pillar to both methodologies
        $methodology1->pillars()->attach($pillar->id, ['section' => 'first']);
        $methodology2->pillars()->attach($pillar->id, ['section' => 'first']);

        // Attach different questions to the same pillar in different methodologies
        \DB::table('pillar_question')->insert([
            'methodology_id' => $methodology1->id,
            'pillar_id' => $pillar->id,
            'question_id' => $question1->id,
            'weight' => 1.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('pillar_question')->insert([
            'methodology_id' => $methodology2->id,
            'pillar_id' => $pillar->id,
            'question_id' => $question2->id,
            'weight' => 2.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test the repository
        $questionRepo = new QuestionRepository();

        // Get questions for pillar in methodology 1
        $questions1 = $questionRepo->getQuestionsByContext('pillar', $pillar->id, $methodology1->id);
        $this->assertCount(1, $questions1);
        $this->assertEquals('Question for Methodology 1', $questions1->first()->title);

        // Get questions for pillar in methodology 2
        $questions2 = $questionRepo->getQuestionsByContext('pillar', $pillar->id, $methodology2->id);
        $this->assertCount(1, $questions2);
        $this->assertEquals('Question for Methodology 2', $questions2->first()->title);

        // Test the API endpoints
        $controller = new QuestionController($questionRepo);

        // Test methodology-aware pillar questions endpoint
        $response1 = $controller->getPillarQuestionsForMethodology($methodology1->id, $pillar->id);
        $this->assertEquals(200, $response1->getStatusCode());
        $data1 = json_decode($response1->getContent(), true);
        $this->assertCount(1, $data1);
        $this->assertEquals('Question for Methodology 1', $data1[0]['title']);

        $response2 = $controller->getPillarQuestionsForMethodology($methodology2->id, $pillar->id);
        $this->assertEquals(200, $response2->getStatusCode());
        $data2 = json_decode($response2->getContent(), true);
        $this->assertCount(1, $data2);
        $this->assertEquals('Question for Methodology 2', $data2[0]['title']);
    }

    public function test_module_questions_are_methodology_aware()
    {
        // Create two methodologies
        $methodology1 = Methodology::create([
            'name' => 'Methodology 1',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'complex',
        ]);

        $methodology2 = Methodology::create([
            'name' => 'Methodology 2',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'complex',
        ]);

        // Create a shared module
        $module = Module::create(['name' => 'Shared Module', 'description' => 'Test', 'definition' => 'Test']);

        // Create questions
        $question1 = Question::create(['title' => 'Module Question for Methodology 1', 'type' => 'MCQMultiple']);
        $question2 = Question::create(['title' => 'Module Question for Methodology 2', 'type' => 'Rating1to5']);

        // Create answers
        $optionA = Answer::create(['title' => 'Option A']);
        $optionB = Answer::create(['title' => 'Option B']);
        $rating1 = Answer::create(['title' => '1']);
        $rating2 = Answer::create(['title' => '2']);

        // Attach answers to questions
        $question1->answers()->attach([$optionA->id, $optionB->id]);
        $question2->answers()->attach([$rating1->id, $rating2->id]);

        // Attach module to both methodologies
        $methodology1->modules()->attach($module->id);
        $methodology2->modules()->attach($module->id);

        // Attach different questions to the same module in different methodologies
        \DB::table('module_question')->insert([
            'methodology_id' => $methodology1->id,
            'module_id' => $module->id,
            'pillar_id' => null,
            'question_id' => $question1->id,
            'weight' => 1.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('module_question')->insert([
            'methodology_id' => $methodology2->id,
            'module_id' => $module->id,
            'pillar_id' => null,
            'question_id' => $question2->id,
            'weight' => 2.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test the repository
        $questionRepo = new QuestionRepository();

        // Get questions for module in methodology 1
        $questions1 = $questionRepo->getQuestionsByContext('module', $module->id, $methodology1->id);
        $this->assertCount(1, $questions1);
        $this->assertEquals('Module Question for Methodology 1', $questions1->first()->title);

        // Get questions for module in methodology 2
        $questions2 = $questionRepo->getQuestionsByContext('module', $module->id, $methodology2->id);
        $this->assertCount(1, $questions2);
        $this->assertEquals('Module Question for Methodology 2', $questions2->first()->title);

        // Test the API endpoints
        $controller = new QuestionController($questionRepo);

        // Test methodology-aware module questions endpoint
        $response1 = $controller->getModuleQuestionsForMethodology($methodology1->id, $module->id);
        $this->assertEquals(200, $response1->getStatusCode());
        $data1 = json_decode($response1->getContent(), true);
        $this->assertCount(1, $data1);
        $this->assertEquals('Module Question for Methodology 1', $data1[0]['title']);

        $response2 = $controller->getModuleQuestionsForMethodology($methodology2->id, $module->id);
        $this->assertEquals(200, $response2->getStatusCode());
        $data2 = json_decode($response2->getContent(), true);
        $this->assertCount(1, $data2);
        $this->assertEquals('Module Question for Methodology 2', $data2[0]['title']);
    }
} 