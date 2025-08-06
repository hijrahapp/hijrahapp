<?php

namespace Tests\Feature;

use App\Http\Controllers\QuestionController;
use App\Http\Repositories\QuestionRepository;
use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Question;
use App\Models\QuestionAnswerWeight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_methodology_questions_api_returns_weights()
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

        // Test the controller directly
        $questionRepo = new QuestionRepository();
        $controller = new QuestionController($questionRepo);
        
        $response = $controller->getMethodologyQuestions($methodology->id);
        
        // Verify response structure
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        
        // Verify we have questions
        $this->assertNotEmpty($data);
        
        // Verify the first question has answers with weights
        $firstQuestion = $data[0];
        $this->assertEquals('Test Question', $firstQuestion['title']);
        $this->assertEquals('single', $firstQuestion['type']);
        
        // Verify answers have weights
        $this->assertCount(2, $firstQuestion['answers']);
        
        $yesAnswerData = collect($firstQuestion['answers'])->firstWhere('title', 'Yes');
        $noAnswerData = collect($firstQuestion['answers'])->firstWhere('title', 'No');
        
        $this->assertNotNull($yesAnswerData);
        $this->assertNotNull($noAnswerData);
        $this->assertEquals(2.5, $yesAnswerData['weight']);
        $this->assertEquals(1.0, $noAnswerData['weight']);
    }
} 