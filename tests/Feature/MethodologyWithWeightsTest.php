<?php

namespace Tests\Feature;

use App\Http\Controllers\MethodologyController;
use App\Http\Repositories\MethodologyRepository;
use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use App\Models\QuestionAnswerWeight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MethodologyWithWeightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_methodology_endpoint_returns_weights_for_all_questions()
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

        // Create answers
        $yesAnswer = Answer::create(['title' => 'Yes']);
        $noAnswer = Answer::create(['title' => 'No']);
        $trueAnswer = Answer::create(['title' => 'True']);
        $falseAnswer = Answer::create(['title' => 'False']);
        $optionA = Answer::create(['title' => 'Option A']);
        $optionB = Answer::create(['title' => 'Option B']);

        // Attach answers to questions
        $methodologyQuestion->answers()->attach([$yesAnswer->id, $noAnswer->id]);
        $pillarQuestion->answers()->attach([$trueAnswer->id, $falseAnswer->id]);
        $moduleQuestion->answers()->attach([$optionA->id, $optionB->id]);

        // Create pillar and module
        $pillar = Pillar::create(['name' => 'Test Pillar', 'description' => 'Test', 'definition' => 'Test']);
        $module = Module::create(['name' => 'Test Module', 'description' => 'Test', 'definition' => 'Test']);

        // Attach relationships
        $methodology->questions()->attach($methodologyQuestion->id);
        $methodology->pillars()->attach($pillar->id, ['section' => 'first']);
        $pillar->questions()->attach($pillarQuestion->id);
        $pillar->modules()->attach($module->id);
        $module->questions()->attach($moduleQuestion->id);

        // Get pivot IDs
        $methodologyPivotId = \DB::table('methodology_question')
            ->where('methodology_id', $methodology->id)
            ->where('question_id', $methodologyQuestion->id)
            ->value('id');

        $pillarPivotId = \DB::table('pillar_question')
            ->where('pillar_id', $pillar->id)
            ->where('question_id', $pillarQuestion->id)
            ->value('id');

        $modulePivotId = \DB::table('module_question')
            ->where('module_id', $module->id)
            ->where('question_id', $moduleQuestion->id)
            ->value('id');

        // Create weights
        QuestionAnswerWeight::create([
            'context_type' => 'methodology_question',
            'context_id' => $methodologyPivotId,
            'answer_id' => $yesAnswer->id,
            'weight' => 2.5,
        ]);

        QuestionAnswerWeight::create([
            'context_type' => 'methodology_question',
            'context_id' => $methodologyPivotId,
            'answer_id' => $noAnswer->id,
            'weight' => 1.0,
        ]);

        QuestionAnswerWeight::create([
            'context_type' => 'pillar_question',
            'context_id' => $pillarPivotId,
            'answer_id' => $trueAnswer->id,
            'weight' => 3.0,
        ]);

        QuestionAnswerWeight::create([
            'context_type' => 'pillar_question',
            'context_id' => $pillarPivotId,
            'answer_id' => $falseAnswer->id,
            'weight' => 0.5,
        ]);

        QuestionAnswerWeight::create([
            'context_type' => 'module_question',
            'context_id' => $modulePivotId,
            'answer_id' => $optionA->id,
            'weight' => 1.5,
        ]);

        QuestionAnswerWeight::create([
            'context_type' => 'module_question',
            'context_id' => $modulePivotId,
            'answer_id' => $optionB->id,
            'weight' => 2.0,
        ]);

        // Test the repository
        $methodologyRepo = new MethodologyRepository();
        $methodologyWithWeights = $methodologyRepo->findByIdWithFullDetails($methodology->id);

        // Verify methodology questions have weights
        $this->assertNotEmpty($methodologyWithWeights->questions);
        $firstMethodologyQuestion = $methodologyWithWeights->questions->first();
        $this->assertTrue(isset($firstMethodologyQuestion->answer_weights));
        $this->assertEquals(2.5, $firstMethodologyQuestion->answer_weights->get($yesAnswer->id)->weight);

        // Verify pillar questions have weights
        $this->assertNotEmpty($methodologyWithWeights->pillars);
        $firstPillar = $methodologyWithWeights->pillars->first();
        $this->assertNotEmpty($firstPillar->questions);
        $firstPillarQuestion = $firstPillar->questions->first();
        $this->assertTrue(isset($firstPillarQuestion->answer_weights));
        $this->assertEquals(3.0, $firstPillarQuestion->answer_weights->get($trueAnswer->id)->weight);

        // Verify module questions have weights
        $this->assertNotEmpty($firstPillar->modules);
        $firstModule = $firstPillar->modules->first();
        $this->assertNotEmpty($firstModule->questions);
        $firstModuleQuestion = $firstModule->questions->first();
        $this->assertTrue(isset($firstModuleQuestion->answer_weights));
        $this->assertEquals(1.5, $firstModuleQuestion->answer_weights->get($optionA->id)->weight);
    }
} 