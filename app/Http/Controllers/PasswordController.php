<?php

namespace App\Http\Controllers;

use App\Http\Services\OTPService;
use App\Http\Services\UserService;
use Illuminate\Http\Request;

class PasswordController
{
    public function __construct(private UserService $userService, private OTPService $otpService) {}

    public function forgetPassword(Request $request) {
        return $this->otpService->resendPasswordOTP($request['email']);
    }

    public function verifyOTP(Request $request) {
        return $this->otpService->verifyPasswordOTP($request['email'], $request['otp']);
    }

    public function resetPassword(Request $request) {
        return $this->userService->resetPassword($request->authUser, $request->password);
    }
}
