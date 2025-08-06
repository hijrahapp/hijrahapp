<?php

namespace Tests\Feature;

use App\Http\Repositories\QuestionRepository;
use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Question;
use App\Models\QuestionAnswerWeight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionsWithWeightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_methodology_questions_with_weights()
    {
        // Create a methodology
        $methodology = Methodology::create([
            'name' => 'Test Methodology',
            'description' => 'Test description',
            'definition' => 'Test definition',
            'type' => 'simple',
        ]);

        // Create a question
        $question = Question::create([
            'title' => 'Test Question',
            'type' => 'YesNo',
        ]);

        // Create answers
        $yesAnswer = Answer::create(['title' => 'Yes']);
        $noAnswer = Answer::create(['title' => 'No']);

        // Attach answers to question
        $question->answers()->attach([$yesAnswer->id, $noAnswer->id]);

        // Attach question to methodology
        $methodology->questions()->attach($question->id);

        // Get the pivot ID
        $pivotId = \DB::table('methodology_question')
            ->where('methodology_id', $methodology->id)
            ->where('question_id', $question->id)
            ->value('id');

        // Create weights for answers
        QuestionAnswerWeight::create([
            'context_type' => 'methodology_question',
            'context_id' => $pivotId,
            'answer_id' => $yesAnswer->id,
            'weight' => 2.5,
        ]);

        QuestionAnswerWeight::create([
            'context_type' => 'methodology_question',
            'context_id' => $pivotId,
            'answer_id' => $noAnswer->id,
            'weight' => 1.0,
        ]);

        // Test the repository directly
        $questionRepo = new QuestionRepository();
        $questions = $questionRepo->getQuestionsByContext('methodology', $methodology->id);

        // Verify we have questions
        $this->assertNotEmpty($questions);
        
        // Verify the first question has answers with weights
        $firstQuestion = $questions->first();
        $this->assertEquals('Test Question', $firstQuestion->title);
        $this->assertEquals('YesNo', $firstQuestion->type->value);
        
        // Verify answers have weights
        $this->assertCount(2, $firstQuestion->answers);
        
        // Check that weights are attached
        $this->assertTrue(isset($firstQuestion->answer_weights));
        $this->assertCount(2, $firstQuestion->answer_weights);
        
        // Verify specific weights
        $this->assertEquals(2.5, $firstQuestion->answer_weights->get($yesAnswer->id)->weight);
        $this->assertEquals(1.0, $firstQuestion->answer_weights->get($noAnswer->id)->weight);
    }
} 