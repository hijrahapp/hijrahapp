<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        // collect(['SuperAdmin', 'Admin', 'Expert', 'Customer'])->each(fn ($roleName) => Role::factory()->create(['name' => $roleName]));

        // Seed answers and demo methodologies
        $this->call([
            AnswerSeeder::class,
            InterestSeeder::class,
            // Choose one of the following seeders (comment out the one you don't need):
            DemoArabicSeeder::class, // Arabic demo data
            // DemoSeeder::class,        // English demo data
        ]);
    }
}
