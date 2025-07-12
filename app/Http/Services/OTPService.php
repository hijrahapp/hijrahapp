<?php

namespace App\Http\Services;

use App\Http\Repositories\RoleRepository;
use App\Http\Repositories\UserRepository;
use App\Models\User;
use App\Utils\JWTUtils;
use Carbon\Carbon;

class OTPService
{
    public function __construct(private UserRepository $userRepo) {}

    public function sendOTP(User $user) {
        $user->otp = random_int(100000, 999999);
        $user->otp_expires_at = Carbon::now()->addMinutes(15);
        $user->save();

        //MailService sendOTP Mail.
    }

    public function resendOTP(string $userId) {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized user'], 401);
        }

        $this->sendOTP($user);

        return response()->json(null, 201);
    }

    public function verifyOTP(string $userId, string $otp) {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized user'], 401);
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

    public function resendPasswordOTP(string $userEmail) {
        $user = $this->userRepo->findByEmail($userEmail);

        if ($user) {
            $this->sendOTP($user);
        }
    }

    public function verifyPasswordOTP(string $userEmail, string $otp) {
        $user = $this->userRepo->findByEmail($userEmail);

        if(!$user) {
            return response()->json(['message' => 'Unauthorized user'], 401);
        }

        if($user->otp !== $otp) {
            return response()->json(['message' => 'Invalid OTP'], 401);
        }

        if (Carbon::now()->timestamp > $user->otp_expires_at->timestamp) {
            return response()->json(['message' => 'OTP expired'], 401);
        }
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(JWTUtils::generateTempTokenResponse($user));
    }
}
