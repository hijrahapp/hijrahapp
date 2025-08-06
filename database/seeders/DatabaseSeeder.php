<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        collect(['SuperAdmin', 'Admin', 'Expert', 'Customer'])->each(fn ($roleName) => Role::factory()->create(['name' => $roleName]));

        // Seed answers and demo methodologies
        $this->call([
            AnswerSeeder::class,
            DemoMethodologiesSeeder::class,
        ]);
    }
}
