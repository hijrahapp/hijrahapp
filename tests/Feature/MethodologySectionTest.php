<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Pillar;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MethodologySectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_methodology_with_section_1_pillars()
    {
        // Create answers
        $yesAnswer = Answer::factory()->create(['title' => 'Yes']);
        $noAnswer = Answer::factory()->create(['title' => 'No']);

        // Create a methodology
        $methodology = Methodology::factory()->create([
            'name' => 'Test Methodology',
            'type' => 'twoSection',
            'first_section_name' => 'Section 1',
            'second_section_name' => 'Section 2'
        ]);

        // Create pillars for section 1
        $pillar1 = Pillar::factory()->create(['name' => 'Section 1 Pillar 1']);
        $pillar2 = Pillar::factory()->create(['name' => 'Section 1 Pillar 2']);
        
        // Create pillars for section 2
        $pillar3 = Pillar::factory()->create(['name' => 'Section 2 Pillar 1']);
        $pillar4 = Pillar::factory()->create(['name' => 'Section 2 Pillar 2']);

        // Attach pillars to methodology with sections
        $methodology->pillars()->attach($pillar1->id, ['section' => 'first']);
        $methodology->pillars()->attach($pillar2->id, ['section' => 'first']);
        $methodology->pillars()->attach($pillar3->id, ['section' => 'second']);
        $methodology->pillars()->attach($pillar4->id, ['section' => 'second']);

        // Create questions for pillars
        $question1 = Question::factory()->create(['title' => 'Section 1 Question']);
        $question2 = Question::factory()->create(['title' => 'Section 2 Question']);

        $pillar1->questions()->attach($question1->id);
        $pillar3->questions()->attach($question2->id);

        // Attach answers
        $question1->answers()->attach([$yesAnswer->id, $noAnswer->id]);
        $question2->answers()->attach([$yesAnswer->id, $noAnswer->id]);

        $response = $this->getJson("/api/methodology/{$methodology->id}/section/1");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'type',
                        'pillars' => [
                            '*' => [
                                'id',
                                'name',
                                'section',
                                'questions' => [
                                    '*' => [
                                        'id',
                                        'title',
                                        'answers' => [
                                            '*' => [
                                                'id',
                                                'title'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'message'
                ]);

        $this->assertTrue($response->json('success'));
        
        $data = $response->json('data');
        $pillars = $data['pillars'];
        
        // Should only have section 1 pillars
        $this->assertCount(2, $pillars);
        $this->assertEquals('Section 1 Pillar 1', $pillars[0]['name']);
        $this->assertEquals('Section 1 Pillar 2', $pillars[1]['name']);
        
        // All pillars should have section 'first'
        foreach ($pillars as $pillar) {
            $this->assertEquals('first', $pillar['section']);
        }
    }

    public function test_can_fetch_methodology_with_section_2_pillars()
    {
        // Create a methodology
        $methodology = Methodology::factory()->create([
            'name' => 'Test Methodology',
            'type' => 'twoSection'
        ]);

        // Create pillars for section 2
        $pillar1 = Pillar::factory()->create(['name' => 'Section 2 Pillar 1']);
        $pillar2 = Pillar::factory()->create(['name' => 'Section 2 Pillar 2']);

        // Attach pillars to methodology with sections
        $methodology->pillars()->attach($pillar1->id, ['section' => 'second']);
        $methodology->pillars()->attach($pillar2->id, ['section' => 'second']);

        $response = $this->getJson("/api/methodology/{$methodology->id}/section/2");

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $pillars = $data['pillars'];
        
        // Should only have section 2 pillars
        $this->assertCount(2, $pillars);
        $this->assertEquals('Section 2 Pillar 1', $pillars[0]['name']);
        $this->assertEquals('Section 2 Pillar 2', $pillars[1]['name']);
        
        // All pillars should have section 'second'
        foreach ($pillars as $pillar) {
            $this->assertEquals('second', $pillar['section']);
        }
    }

    public function test_returns_400_for_invalid_section_number()
    {
        $methodology = Methodology::factory()->create();

        $response = $this->getJson("/api/methodology/{$methodology->id}/section/3");

        $response->assertStatus(400)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);

        $this->assertFalse($response->json('success'));
    }

    public function test_returns_404_for_nonexistent_methodology()
    {
        $response = $this->getJson("/api/methodology/999/section/1");

        $response->assertStatus(404)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);

        $this->assertFalse($response->json('success'));
    }

    public function test_returns_400_for_non_two_section_methodology()
    {
        // Create a methodology that is not twoSection type
        $methodology = Methodology::factory()->create([
            'type' => 'simple'
        ]);

        $response = $this->getJson("/api/methodology/{$methodology->id}/section/1");

        $response->assertStatus(400)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);

        $this->assertFalse($response->json('success'));
        $this->assertEquals('Methodology must be of two-section type to use section endpoints', $response->json('message'));
    }
} 