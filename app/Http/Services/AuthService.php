<?php

namespace App\Http\Services;

use App\Enums\RoleName;
use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Utils\JWTUtils;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private UserRepository $userRepo, private RoleRepository $roleRepo, private OTPService $otpService) {}

    public function login(string $email, string $password) {
        $user = $this->userRepo->findByEmail($email);
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        if (!$user->active) {
            return response()->json(['message' => 'Inactive user'], 401);
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

        $this->otpService->sendOTP($user);

        return response()->json(JWTUtils::generateTokenResponse($user), 201);
    }
}
