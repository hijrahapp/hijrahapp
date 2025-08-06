<?php

namespace Tests\Feature;

use App\Models\Methodology;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MethodologyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_all_methodologies()
    {
        // Create some test methodologies
        Methodology::factory()->count(3)->create();

        $response = $this->getJson('/api/methodology/all');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'definition',
                            'objectives',
                            'type',
                            'first_section_name',
                            'second_section_name',
                            'pillars_definition',
                            'modules_definition',
                            'tags',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'message'
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_fetch_single_methodology()
    {
        $methodology = Methodology::factory()->create();

        $response = $this->getJson("/api/methodology/{$methodology->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'definition',
                        'objectives',
                        'type',
                        'first_section_name',
                        'second_section_name',
                        'pillars_definition',
                        'modules_definition',
                        'tags',
                        'pillars',
                        'modules',
                        'questions',
                        'created_at',
                        'updated_at'
                    ],
                    'message'
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals($methodology->id, $response->json('data.id'));
    }

    public function test_returns_404_for_nonexistent_methodology()
    {
        $response = $this->getJson('/api/methodology/999');

        $response->assertStatus(404)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);

        $this->assertFalse($response->json('success'));
    }
} 