<?php

namespace App\Http\Services;

use App\Enums\RoleName;
use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Mail\OtpMail;
use App\Utils\JWTUtils;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
        $user = $this->userRepo->findByEmail($request['email']);
        if ($user) {
            if($user->active) {
                return response()->json(['message' => 'Email already exists'], 401);
            }

            $this->userRepo->delete($user);
        }

        $customerRole = $this->roleRepo->findByRoleName(RoleName::Customer);
        $request['roleId'] = $customerRole->id;

        $request['active'] = false;

        $user = $this->userRepo->create($request);

        $this->otpService->generateOTP($user);

        if(config('app.features.email_verification')) {
            // Send OTP email
            Mail::to($user->email)->send(new OtpMail($user->otp, $user, $user->otp_expires_at));
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
        return response()->json(["message" => "Signup complete."]);
    }
}
