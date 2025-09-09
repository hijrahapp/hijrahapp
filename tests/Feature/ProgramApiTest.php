<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProgramApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
    }

    /**
     * Test GET /api/programs/all endpoint returns successful response
     */
    public function test_get_all_programs_returns_successful_response(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Create JWT token for authentication
        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->get('/api/programs/all');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'definition',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    /**
     * Test GET /api/programs/{id} endpoint returns program details
     */
    public function test_get_program_details_returns_successful_response(): void
    {
        // Create a test user and program
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $program = Program::factory()->create();

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->get("/api/programs/{$program->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'definition',
                'objectives',
                'objectives_count',
                'modules',
                'modules_count',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'id' => $program->id,
                'name' => $program->name,
            ]);
    }

    /**
     * Test POST /api/programs/{id}/start endpoint creates user-program relationship
     */
    public function test_start_program_creates_user_program_relationship(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $program = Program::factory()->create();

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->post("/api/programs/{$program->id}/start");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify the relationship was created
        $this->assertDatabaseHas('user_programs', [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Test PUT /api/programs/{id}/complete endpoint updates program status
     */
    public function test_complete_program_updates_status_to_completed(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+1234567890',
            'password' => bcrypt('password123'),
        ]);

        $program = Program::factory()->create();

        // Start the program first
        $program->users()->attach($user->id, [
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->put("/api/programs/{$program->id}/complete");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify the status was updated
        $this->assertDatabaseHas('user_programs', [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Test GET /api/programs/my endpoint returns user's programs
     */
    public function test_get_my_programs_returns_user_programs(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();

        // Associate programs with user
        $program1->users()->attach($user->id, [
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $program2->users()->attach($user->id, [
            'status' => 'completed',
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(2),
        ]);

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->get('/api/programs/my');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'definition',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test authentication is required for protected endpoints
     */
    public function test_authentication_required_for_protected_endpoints(): void
    {
        $program = Program::factory()->create();

        // Test without authorization header
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('/api/programs/all');

        $response->assertStatus(401);

        // Test with invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
            'Accept' => 'application/json',
        ])->get("/api/programs/{$program->id}");

        $response->assertStatus(401);
    }

    /**
     * Test program not found returns 404
     */
    public function test_program_not_found_returns_404(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->get('/api/programs/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }
}
