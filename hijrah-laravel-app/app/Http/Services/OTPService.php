<?php

namespace App\Http\Services;

use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Utils\JWTUtils;
use Carbon\Carbon;

class OTPService
{
    public function __construct(private UserRepository $userRepo) {}

    public function generateOTP(string $userId) {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->otp = random_int(100000, 999999);
        $user->otp_expires_at = Carbon::now()->addMinutes(2);

        //MailService sendOTP Mail.

        $user->save();

        return response()->json(null, 201);
    }

    public function verifyOTP(string $userId, string $otp) {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if($user->otp !== $otp) {
            return response()->json(['message' => 'Invalid OTP'], 401);
        }

        if (Carbon::now()->timestamp > $user->otp_expires_at->timestamp) {
            return response()->json(['message' => 'OTP expired'], 401);
        }

        $user->otp = null;
        $user->otp_expires_at = null;
        if(!$user->active) {
            $user->active = true;
            $user->email_verified_at = Carbon::now();
        }
        $user->save();

        return response()->json(JWTUtils::generateTokenResponse($user));

    }
}
