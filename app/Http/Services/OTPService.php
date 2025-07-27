<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Mail\OtpMail;
use App\Mail\PasswordResetAttemptMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Utils\JWTUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class OTPService
{
    public function __construct(private UserRepository $userRepo) {}

    public function generateOTP(User $user) {
        $user->otp = random_int(1000, 9999);
        $user->otp_expires_at = Carbon::now()->addMinutes(15);
        $user->save();
    }

    public function resendOTP(int $userId) {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return response()->json(['message' => __('messages.unauthorized_user')], 401);
        }

        $this->generateOTP($user);

        if(config('app.features.email_verification')) {
            // Send OTP email
            Mail::to($user->email)->send(new OtpMail($user->otp, $user, $user->otp_expires_at));
        }

        return response()->json(null, 201);
    }

    public function verifyOTP(int $userId, string $otp) {
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

            // Send welcome email for newly activated users
            if(config('app.features.email_verification')) {
                Mail::to($user->email)->send(new WelcomeMail($user));
            }
        }
        $user->save();

        return response()->json(JWTUtils::generateTokenResponse($user));

    }

    public function resendPasswordOTP(string $userEmail) {
        $user = $this->userRepo->findByEmail($userEmail);
        if (!$user) {
            return response()->json(['message' => __('messages.email_not_exists')], 404);
        }

        $this->generateOTP($user);
        // Send password reset attempt email
        if(config('app.features.email_verification')) {
            Mail::to($user->email)->send(new PasswordResetAttemptMail($user, $user->otp, $user->otp_expires_at));
        }

        return response()->json(['message' => __('messages.otp_sent')], 201);
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
            return response()->json(['message' => __('messages.unauthorized_user')], 401);
        }

        if(!$user->otp) {
            return response()->json(['message' => __('messages.nothing_to_verify')], 404);
        }

        if(Carbon::now()->timestamp > $user->otp_expires_at->timestamp) {
            return response()->json(['message' => __('messages.otp_expired')], 406);
        }

        if(config('app.features.email_verification') && $user->otp !== $otp) {
            return response()->json(['message' => __('messages.invalid_otp')], 406);
        }
    }
}
