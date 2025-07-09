<?php

namespace App\Http\Repositories;

use App\Models\User;
use Illuminate\Support\Str;

class UserRepository
{
    public function create(array $data): User {
        $data['id'] = Str::uuid();
        return User::create($data);
    }

    public function getAll() {
        return User::with('role')->get();
    }

    public function findById(string $userId): ?User {
        return User::find($userId);
    }

    public function findByEmail(string $email): ?User {
        return User::where('email', $email)->first();
    }
}
