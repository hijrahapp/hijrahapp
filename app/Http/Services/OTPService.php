<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Mail\OtpMail;
use App\Models\User;
use App\Utils\JWTUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class OTPService
{
    public function __construct(private UserRepository $userRepo) {}

    public function sendOTP(User $user) {
        $user->otp = random_int(100000, 999999);
        $user->otp_expires_at = Carbon::now()->addMinutes(15);
        $user->save();

        if(config('app.features.email_verification')) {
            // Send OTP email
//            Mail::to($user->email)->send(new OtpMail($user->otp, $user, $user->otp_expires_at));
        }
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

        $validate = $this->validate($user, $otp);
        if($validate) {
            return $validate;
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

        $validate = $this->validate($user, $otp);
        if($validate) {
            return $validate;
        }

        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(JWTUtils::generateTempTokenResponse($user));
    }

    private function validate($user, $otp)
    {
        if(!$user) {
            return response()->json(['message' => 'Unauthorized user'], 401);
        }

        if(!$user->otp) {
            return response()->json(['message' => 'Nothing to verify'], 404);
        }

        if(Carbon::now()->timestamp > $user->otp_expires_at->timestamp) {
            return response()->json(['message' => 'OTP expired'], 401);
        }

        if(config('app.features.email_verification') && $user->otp !== $otp) {
            return response()->json(['message' => 'Invalid OTP'], 401);
        }
    }
}
