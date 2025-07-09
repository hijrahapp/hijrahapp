<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use App\Http\Services\FirebaseService;
use App\Http\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{

    public function __construct(private AuthService $authService, private OTPService $otpService, private FirebaseService $firebaseService) {}

    public function login(Request $request) {
        return $this->authService->login($request->email, $request->password);
    }

    public function signup(Request $request) {
        return $this->authService->signup($request->all());
    }

    public function generateOTP(Request $request) {
        return $this->otpService->generateOTP($request->authUserId);
    }

    public function verifyOTP(Request $request) {
        return $this->otpService->verifyOTP($request->authUserId, $request->otp);
    }

    public function firebaseLogin(Request $request) {
        return $this->firebaseService->login($request);
    }
}
