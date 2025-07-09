<?php

namespace App\Http\Repositories;

use App\Enums\RoleName;
use App\Models\Role;

class RoleRepository
{
    function findByName(string $roleName): ?Role {
        return $this->findByRoleName(RoleName::from($roleName));
    }

    function findByRoleName(RoleName $roleName): ?Role {
        return Role::where('name', $roleName)->firstOrFail();
    }
}
