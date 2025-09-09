<?php

namespace Tests\Unit;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Program;
use App\Models\Question;
use App\Models\Step;
use App\Resources\StepDetailedResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class StepDetailedResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_step_detailed_resource_includes_questions_for_quiz_steps(): void
    {
        // Create a program and quiz step
        $program = Program::factory()->create();
        $step = Step::factory()->create([
            'program_id' => $program->id,
            'type' => 'quiz',
        ]);

        // Create questions with answers
        $question1 = Question::factory()->create([
            'title' => 'First Question',
            'type' => QuestionType::MCQSingle,
        ]);

        $question2 = Question::factory()->create([
            'title' => 'Second Question',
            'type' => QuestionType::MCQSingle,
        ]);

        $answer1 = Answer::factory()->create(['title' => 'Answer 1']);
        $answer2 = Answer::factory()->create(['title' => 'Answer 2']);

        // Attach answers to questions
        $question1->answers()->attach([$answer1->id, $answer2->id]);
        $question2->answers()->attach([$answer1->id, $answer2->id]);

        // Attach questions to step with different sequences
        $step->questions()->attach($question2->id, [
            'correct_answer_id' => $answer1->id,
            'sequence' => 2,
        ]);

        $step->questions()->attach($question1->id, [
            'correct_answer_id' => $answer2->id,
            'sequence' => 1,
        ]);

        // Load the relationships
        $step->load(['questions.answers']);

        // Create the resource
        $request = new Request;
        $resource = new StepDetailedResource($step);
        $array = $resource->toArray($request);

        // Assert the structure
        $this->assertArrayHasKey('questions', $array);
        $this->assertIsArray($array['questions']);
        $this->assertCount(2, $array['questions']);

        // Assert questions are in sequence order (question1 first, then question2)
        $this->assertEquals($question1->id, $array['questions'][0]['id']);
        $this->assertEquals('First Question', $array['questions'][0]['title']);

        $this->assertEquals($question2->id, $array['questions'][1]['id']);
        $this->assertEquals('Second Question', $array['questions'][1]['title']);

        // Assert answers data for first question
        $answers1 = $array['questions'][0]['answers'];
        $this->assertCount(2, $answers1);
        $this->assertEquals($answer1->id, $answers1[0]['id']);
        $this->assertFalse($answers1[0]['is_correct']);
        $this->assertEquals($answer2->id, $answers1[1]['id']);
        $this->assertTrue($answers1[1]['is_correct']);

        // Assert answers data for second question
        $answers2 = $array['questions'][1]['answers'];
        $this->assertCount(2, $answers2);
        $this->assertEquals($answer1->id, $answers2[0]['id']);
        $this->assertTrue($answers2[0]['is_correct']);
        $this->assertEquals($answer2->id, $answers2[1]['id']);
        $this->assertFalse($answers2[1]['is_correct']);
    }

    public function test_step_detailed_resource_does_not_include_questions_for_non_quiz_steps(): void
    {
        // Create a program and non-quiz step
        $program = Program::factory()->create();
        $step = Step::factory()->create([
            'program_id' => $program->id,
            'type' => 'journal',
        ]);

        // Create the resource
        $request = new Request;
        $resource = new StepDetailedResource($step);
        $array = $resource->toArray($request);

        // Assert questions are not included
        $this->assertArrayNotHasKey('questions', $array);
    }

    public function test_step_detailed_resource_does_not_include_questions_when_not_loaded(): void
    {
        // Create a program and quiz step
        $program = Program::factory()->create();
        $step = Step::factory()->create([
            'program_id' => $program->id,
            'type' => 'quiz',
        ]);

        // Don't load the questions relationship

        // Create the resource
        $request = new Request;
        $resource = new StepDetailedResource($step);
        $array = $resource->toArray($request);

        // Assert questions are not included when relationship is not loaded
        $this->assertArrayNotHasKey('questions', $array);
    }
}
