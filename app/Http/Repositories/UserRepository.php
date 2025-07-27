<?php

namespace App\Http\Repositories;

use App\Models\User;

class UserRepository
{
    public function create(array $data): User {
        return User::create($data);
    }

    public function getAll() {
        return User::with('role')->get();
    }

    public function findById(int $userId): ?User {
        return User::find($userId);
    }

    public function findByEmail(string $email): ?User {
        return User::where('email', $email)->first();
    }

    public function update(int $userId, array $data): bool {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        return $user->update($data);
    }

    public function findByFirebaseUid(string $firebaseUid): ?User {
        return User::where('firebase_uid', $firebaseUid)->first();
    }

    public function delete($user): bool {
        return $user->delete();
    }
}
