<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // default password
            'otp' => null,
            'otp_expires_at' => null,
            'active' => $this->faker->boolean(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'birthDate' => $this->faker->date('Y-m-d', '2005-01-01'),
            'roleId' => 1, // Use User role for tests
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
