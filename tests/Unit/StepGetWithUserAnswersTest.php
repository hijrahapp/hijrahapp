<?php

namespace Tests\Unit;

use App\Enums\QuestionType;
use App\Http\Controllers\StepController;
use App\Http\Repositories\StepRepository;
use App\Models\Answer;
use App\Models\Program;
use App\Models\Question;
use App\Models\Step;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class StepGetWithUserAnswersTest extends TestCase
{
    use RefreshDatabase;

    private StepController $stepController;

    private StepRepository $stepRepository;

    private User $user;

    private Program $program;

    private Step $step;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic roles for testing
        \DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stepRepository = new StepRepository;
        $this->stepController = new StepController($this->stepRepository);

        // Create test user
        $this->user = User::factory()->create();

        // Create test program
        $this->program = Program::factory()->create();

        // Create quiz step
        $this->step = Step::factory()->create([
            'program_id' => $this->program->id,
            'type' => 'quiz',
        ]);
    }

    public function test_get_step_includes_user_answers_for_quiz_steps(): void
    {
        // Create questions with answers
        $question1 = Question::factory()->create([
            'title' => 'Question 1',
            'type' => QuestionType::MCQSingle,
        ]);

        $question2 = Question::factory()->create([
            'title' => 'Question 2',
            'type' => QuestionType::MCQSingle,
        ]);

        $answer1 = Answer::factory()->create(['title' => 'Answer 1']);
        $answer2 = Answer::factory()->create(['title' => 'Answer 2']);
        $answer3 = Answer::factory()->create(['title' => 'Answer 3']);
        $answer4 = Answer::factory()->create(['title' => 'Answer 4']);

        // Attach answers to questions
        $question1->answers()->attach([$answer1->id, $answer2->id]);
        $question2->answers()->attach([$answer3->id, $answer4->id]);

        // Attach questions to step with correct answers
        $this->step->questions()->attach($question1->id, [
            'correct_answer_id' => $answer1->id,
            'sequence' => 1,
        ]);

        $this->step->questions()->attach($question2->id, [
            'correct_answer_id' => $answer4->id,
            'sequence' => 2,
        ]);

        // Create user answers
        UserAnswer::create([
            'user_id' => $this->user->id,
            'context_type' => 'module',
            'context_id' => $this->step->id,
            'question_id' => $question1->id,
            'answer_id' => $answer1->id,
        ]);

        UserAnswer::create([
            'user_id' => $this->user->id,
            'context_type' => 'module',
            'context_id' => $this->step->id,
            'question_id' => $question2->id,
            'answer_id' => $answer3->id,
        ]);

        // Create a mock request with authUser
        $request = new Request;
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->merge(['authUserId' => $this->user->id]);
        $request->authUser = $this->user;

        // Call the get method
        $response = $this->stepController->get($request, $this->program->id, $this->step->id);

        // Assert response
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        // Assert questions are included
        $this->assertArrayHasKey('questions', $responseData);
        $this->assertCount(2, $responseData['questions']);

        // Assert first question has user answers
        $question1Data = $responseData['questions'][0];
        $this->assertEquals($question1->id, $question1Data['id']);
        $this->assertArrayHasKey('user_answers', $question1Data);
        $this->assertCount(1, $question1Data['user_answers']);
        $this->assertEquals($answer1->id, $question1Data['user_answers'][0]['id']);
        $this->assertEquals('Answer 1', $question1Data['user_answers'][0]['text']);

        // Assert second question has user answers
        $question2Data = $responseData['questions'][1];
        $this->assertEquals($question2->id, $question2Data['id']);
        $this->assertArrayHasKey('user_answers', $question2Data);
        $this->assertCount(1, $question2Data['user_answers']);
        $this->assertEquals($answer3->id, $question2Data['user_answers'][0]['id']);
        $this->assertEquals('Answer 3', $question2Data['user_answers'][0]['text']);

        // Assert all answers are still included with is_correct flag
        $this->assertArrayHasKey('answers', $question1Data);
        $this->assertCount(2, $question1Data['answers']);

        // Check that correct answer is marked
        $correctAnswer = collect($question1Data['answers'])->firstWhere('is_correct', true);
        $this->assertNotNull($correctAnswer);
        $this->assertEquals($answer1->id, $correctAnswer['id']);
    }

    public function test_get_step_without_user_answers_returns_empty_user_answers(): void
    {
        // Create a question with answers
        $question = Question::factory()->create([
            'title' => 'Question 1',
            'type' => QuestionType::MCQSingle,
        ]);

        $answer1 = Answer::factory()->create(['title' => 'Answer 1']);
        $answer2 = Answer::factory()->create(['title' => 'Answer 2']);

        // Attach answers to question
        $question->answers()->attach([$answer1->id, $answer2->id]);

        // Attach question to step
        $this->step->questions()->attach($question->id, [
            'correct_answer_id' => $answer1->id,
            'sequence' => 1,
        ]);

        // Create a mock request with authUser
        $request = new Request;
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->merge(['authUserId' => $this->user->id]);
        $request->authUser = $this->user;

        // Call the get method
        $response = $this->stepController->get($request, $this->program->id, $this->step->id);

        // Assert response
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        // Assert questions are included
        $this->assertArrayHasKey('questions', $responseData);
        $this->assertCount(1, $responseData['questions']);

        // Assert question has empty user answers
        $questionData = $responseData['questions'][0];
        $this->assertArrayHasKey('user_answers', $questionData);
        $this->assertCount(0, $questionData['user_answers']);
    }

    public function test_get_step_without_authenticated_user_does_not_include_user_answers(): void
    {
        // Create a question with answers
        $question = Question::factory()->create([
            'title' => 'Question 1',
            'type' => QuestionType::MCQSingle,
        ]);

        $answer1 = Answer::factory()->create(['title' => 'Answer 1']);
        $answer2 = Answer::factory()->create(['title' => 'Answer 2']);

        // Attach answers to question
        $question->answers()->attach([$answer1->id, $answer2->id]);

        // Attach question to step
        $this->step->questions()->attach($question->id, [
            'correct_answer_id' => $answer1->id,
            'sequence' => 1,
        ]);

        // Create a mock request without authUser
        $request = new Request;

        // Call the get method
        $response = $this->stepController->get($request, $this->program->id, $this->step->id);

        // Assert response
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        // Assert questions are included
        $this->assertArrayHasKey('questions', $responseData);
        $this->assertCount(1, $responseData['questions']);

        // Assert question does not have user_answers field
        $questionData = $responseData['questions'][0];
        $this->assertArrayNotHasKey('user_answers', $questionData);
    }
}
