<?php

namespace App\Http\Services;

use App\Enums\RoleName;
use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Mail\SignupMail;
use App\Utils\JWTUtils;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function __construct(private UserRepository $userRepo, private RoleRepository $roleRepo, private OTPService $otpService) {}

    public function adminLogin(string $email, string $password) {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            return response()->json(['message' => __('messages.email_not_exists')], 404);
        }
        if (!Hash::check($password, $user->password)) {
            return response()->json(['message' => __('messages.incorrect_password')], 401);
        }
        if (!$user->active) {
            return response()->json(['message' => __('messages.inactive_user')], 403);
        }
        $roles = ['SuperAdmin', 'Admin', 'Expert'];
        if (!$user->role || !in_array($user->role->name->value, $roles)) {
            return response()->json(['message' => __('messages.unauthorized_role')], 403);
        }

        return response()->json(JWTUtils::generateTokenResponse($user));
    }

    public function login(string $email, string $password) {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            return response()->json(['message' => __('messages.email_not_exists')], 404);
        }
        if (!Hash::check($password, $user->password)) {
            return response()->json(['message' => __('messages.incorrect_password')], 401);
        }
        if (!$user->active) {
            return response()->json(['message' => __('messages.inactive_user')], 403);
        }

        return response()->json(JWTUtils::generateTokenResponse($user));
    }

    public function signup($request) {
        $user = $this->userRepo->findByEmail($request['email']);
        if ($user) {
            if($user->active) {
                return response()->json(['message' => __('messages.email_exists')], 403);
            }

            $this->userRepo->delete($user);
        }

        $customerRole = $this->roleRepo->findByRoleName(RoleName::Customer);
        $request['roleId'] = $customerRole->id;

        $request['active'] = false;

        $user = $this->userRepo->create($request);

        $this->otpService->generateOTP($user);

        if(config('app.features.email_verification')) {
            Mail::to($user->email)->send(new SignupMail($user->otp, $user));
        }

        return response()->json(JWTUtils::generateTokenResponse($user), 201);
    }

    public function completeSignup($user, $request) {
        $updateData = [];
        if (isset($request['gender'])) {
            $updateData['gender'] = $request['gender'];
        }
        if (isset($request['birthDate'])) {
            $updateData['birthDate'] = $request['birthDate'];
        }
        $this->userRepo->update($user->id, $updateData);
        $user->refresh();
        return response()->json(["message" => __('messages.signup_complete')]);
    }
}
