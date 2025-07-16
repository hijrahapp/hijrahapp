<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use App\Http\Services\FirebaseService;
use App\Http\Services\OTPService;
use App\Http\Services\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{

    public function __construct(private AuthService $authService, private OTPService $otpService, private FirebaseService $firebaseService, private GoogleService $googleService) {}

    public function login(Request $request) {
        return $this->authService->login($request->email, $request->password);
    }

    public function signup(Request $request) {
        return $this->authService->signup($request->all());
    }

    public function resendOTP(Request $request) {
        return $this->otpService->resendOTP($request->authUserId);
    }

    public function verifyOTP(Request $request) {
        return $this->otpService->verifyOTP($request->authUserId, $request->otp);
    }

    /**
     * Authenticate user with Firebase token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function firebaseLogin(Request $request) {
        return $this->firebaseService->login($request->all());
    }
    
    /**
     * Authenticate user with Google access token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function googleAccessTokenLogin(Request $request) {
        return $this->googleService->login($request->all());
    }

    public function completeSignup(Request $request) {
        return $this->authService->completeSignup($request->authUser, $request->all());
    }
}
