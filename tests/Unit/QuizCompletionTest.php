<?php

namespace Tests\Unit;

use App\Enums\QuestionType;
use App\Http\Repositories\StepRepository;
use App\Models\Answer;
use App\Models\Program;
use App\Models\Question;
use App\Models\Step;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserStepProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizCompletionTest extends TestCase
{
    use RefreshDatabase;

    private StepRepository $stepRepository;

    private User $user;

    private Program $program;

    private Step $step;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic roles for testing - create Customer role with ID 1
        \DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stepRepository = new StepRepository;

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

    public function test_complete_quiz_step_with_correct_answers(): void
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

        // User answers (all correct)
        $userAnswers = [
            [
                'question_id' => $question1->id,
                'answer_id' => $answer1->id, // Correct
            ],
            [
                'question_id' => $question2->id,
                'answer_id' => $answer4->id, // Correct
            ],
        ];

        // Complete the quiz
        $result = $this->stepRepository->completeStep(
            $this->user->id,
            $this->program->id,
            $this->step->id,
            ['answers' => $userAnswers]
        );

        // Assert success
        $this->assertTrue($result['success']);
        $this->assertEquals('Quiz completed successfully', $result['message']);

        // Assert score data
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(2, $result['data']['score']);
        $this->assertEquals(2, $result['data']['total_questions']);
        $this->assertEquals(2, $result['data']['correct_answers']);
        $this->assertEquals(100.0, $result['data']['percentage']);

        // Assert user progress was updated
        $progress = UserStepProgress::where('user_id', $this->user->id)
            ->where('step_id', $this->step->id)
            ->first();

        $this->assertNotNull($progress);
        $this->assertEquals('completed', $progress->status);
        $this->assertEquals(2, $progress->score);
        $this->assertEquals(100.0, $progress->percentage);

        // Assert user answers were stored
        $storedAnswers = UserAnswer::where('user_id', $this->user->id)
            ->where('context_type', 'module')
            ->where('context_id', $this->step->id)
            ->get();

        $this->assertCount(2, $storedAnswers);
        $this->assertEquals($question1->id, $storedAnswers[0]->question_id);
        $this->assertEquals($answer1->id, $storedAnswers[0]->answer_id);
        $this->assertEquals($question2->id, $storedAnswers[1]->question_id);
        $this->assertEquals($answer4->id, $storedAnswers[1]->answer_id);
    }

    public function test_complete_quiz_step_with_mixed_answers(): void
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

        // User answers (1 correct, 1 wrong)
        $userAnswers = [
            [
                'question_id' => $question1->id,
                'answer_id' => $answer1->id, // Correct
            ],
            [
                'question_id' => $question2->id,
                'answer_id' => $answer3->id, // Wrong
            ],
        ];

        // Complete the quiz
        $result = $this->stepRepository->completeStep(
            $this->user->id,
            $this->program->id,
            $this->step->id,
            ['answers' => $userAnswers]
        );

        // Assert success
        $this->assertTrue($result['success']);

        // Assert score data
        $this->assertEquals(1, $result['data']['score']);
        $this->assertEquals(2, $result['data']['total_questions']);
        $this->assertEquals(1, $result['data']['correct_answers']);
        $this->assertEquals(50.0, $result['data']['percentage']);

        // Assert user progress was updated
        $progress = UserStepProgress::where('user_id', $this->user->id)
            ->where('step_id', $this->step->id)
            ->first();

        $this->assertEquals(1, $progress->score);
        $this->assertEquals(50.0, $progress->percentage);
    }

    public function test_complete_quiz_step_with_incomplete_answers(): void
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

        // Attach answers to questions
        $question1->answers()->attach([$answer1->id, $answer2->id]);
        $question2->answers()->attach([$answer1->id, $answer2->id]);

        // Attach questions to step
        $this->step->questions()->attach($question1->id, [
            'correct_answer_id' => $answer1->id,
            'sequence' => 1,
        ]);

        $this->step->questions()->attach($question2->id, [
            'correct_answer_id' => $answer2->id,
            'sequence' => 2,
        ]);

        // User answers (only 1 answer provided for 2 questions)
        $userAnswers = [
            [
                'question_id' => $question1->id,
                'answer_id' => $answer1->id,
            ],
        ];

        // Complete the quiz
        $result = $this->stepRepository->completeStep(
            $this->user->id,
            $this->program->id,
            $this->step->id,
            ['answers' => $userAnswers]
        );

        // Assert failure
        $this->assertFalse($result['success']);
        $this->assertEquals('All questions must be answered', $result['message']);
    }

    public function test_complete_quiz_step_with_invalid_question(): void
    {
        // Create a question that's not attached to the step
        $question = Question::factory()->create([
            'title' => 'Question 1',
            'type' => QuestionType::MCQSingle,
        ]);

        $answer = Answer::factory()->create(['title' => 'Answer 1']);

        // User answers with invalid question
        $userAnswers = [
            [
                'question_id' => $question->id,
                'answer_id' => $answer->id,
            ],
        ];

        // Complete the quiz
        $result = $this->stepRepository->completeStep(
            $this->user->id,
            $this->program->id,
            $this->step->id,
            ['answers' => $userAnswers]
        );

        // Assert failure
        $this->assertFalse($result['success']);
        $this->assertEquals('No questions found for this quiz step', $result['message']);
    }
}
