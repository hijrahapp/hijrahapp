<?php

namespace App\Http\Services;

use App\Enums\RoleName;
use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Resources\UserResource;
use App\Utils\JWTUtils;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private UserRepository $userRepo, private RoleRepository $roleRepo) {}

    public function login(string $email, string $password) {
        $user = $this->userRepo->findByEmail($email);
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json(JWTUtils::generateTokenResponse($user));
    }

    public function signup($request) {
        if ($this->userRepo->findByEmail($request['email'])) {
            return response()->json(['message' => 'Email already exists'], 401);
        }

        $customerRole = $this->roleRepo->findByRoleName(RoleName::Customer);
        $request['roleId'] = $customerRole->id;

        $request['active'] = false;

        $user = $this->userRepo->create($request);

        return response()->json(JWTUtils::generateTokenResponse($user), 201);
    }
}
