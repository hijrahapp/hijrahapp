<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MethodologyDetailedTest extends TestCase
{
    use RefreshDatabase;

    public function test_detailed_methodology_returns_nested_data_with_answers()
    {
        // Create answers
        $yesAnswer = Answer::factory()->create(['title' => 'Yes']);
        $noAnswer = Answer::factory()->create(['title' => 'No']);

        // Create a methodology
        $methodology = Methodology::factory()->create([
            'name' => 'Test Methodology',
            'type' => 'complex'
        ]);

        // Create a pillar
        $pillar = Pillar::factory()->create(['name' => 'Test Pillar']);
        $methodology->pillars()->attach($pillar->id, ['section' => 'first']);

        // Create a module
        $module = Module::factory()->create(['name' => 'Test Module']);
        $pillar->modules()->attach($module->id);

        // Create questions
        $pillarQuestion = Question::factory()->create([
            'title' => 'Pillar Question',
            'type' => 'YesNo'
        ]);
        $moduleQuestion = Question::factory()->create([
            'title' => 'Module Question',
            'type' => 'YesNo'
        ]);
        $methodologyQuestion = Question::factory()->create([
            'title' => 'Methodology Question',
            'type' => 'YesNo'
        ]);

        // Attach questions to their respective models
        $pillar->questions()->attach($pillarQuestion->id);
        $module->questions()->attach($moduleQuestion->id);
        $methodology->questions()->attach($methodologyQuestion->id);

        // Attach answers to questions
        $pillarQuestion->answers()->attach([$yesAnswer->id, $noAnswer->id]);
        $moduleQuestion->answers()->attach([$yesAnswer->id, $noAnswer->id]);
        $methodologyQuestion->answers()->attach([$yesAnswer->id, $noAnswer->id]);

        $response = $this->getJson("/api/methodology/{$methodology->id}");

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
                                'modules' => [
                                    '*' => [
                                        'id',
                                        'name',
                                        'questions' => [
                                            '*' => [
                                                'id',
                                                'title',
                                                'type',
                                                'answers' => [
                                                    '*' => [
                                                        'id',
                                                        'title'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                'questions' => [
                                    '*' => [
                                        'id',
                                        'title',
                                        'type',
                                        'answers' => [
                                            '*' => [
                                                'id',
                                                'title'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'modules' => [
                            '*' => [
                                'id',
                                'name',
                                'questions' => [
                                    '*' => [
                                        'id',
                                        'title',
                                        'type',
                                        'answers' => [
                                            '*' => [
                                                'id',
                                                'title'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'questions' => [
                            '*' => [
                                'id',
                                'title',
                                'type',
                                'answers' => [
                                    '*' => [
                                        'id',
                                        'title'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'message'
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('Test Methodology', $response->json('data.name'));
        
        // Verify that answers are included
        $data = $response->json('data');
        $this->assertNotEmpty($data['questions'][0]['answers']);
        $this->assertNotEmpty($data['pillars'][0]['questions'][0]['answers']);
        $this->assertNotEmpty($data['pillars'][0]['modules'][0]['questions'][0]['answers']);
    }
} 