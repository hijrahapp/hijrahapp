<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
         collect(['SuperAdmin', 'Admin', 'Expert', 'Customer'])->map(function ($roleName) {
            return Role::factory()->create(['name' => $roleName]);
        });

        // Create 10 users assigned to random roles
//        User::factory(10)->create();
    }
}
