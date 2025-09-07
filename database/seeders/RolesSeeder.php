<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        collect(['SuperAdmin', 'Admin', 'Expert', 'Customer'])->each(fn ($roleName) => Role::factory()->create(['name' => $roleName]));
    }
} 