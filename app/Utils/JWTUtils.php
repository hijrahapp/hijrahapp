<?php

namespace App\Utils;
use App\Resources\UserResource;
use Carbon\Carbon;
use Firebase\JWT\JWT;

class JWTUtils
{
    public static function generateTokenResponse($user) {
        $payload = [
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->name,
            'expiry' => null,
        ];

        $jwtSecret = config('app.jwt_secret');
        if (empty($jwtSecret)) {
            throw new \Exception('JWT_SECRET is not configured. Please check your .env file.');
        }

        $token = JWT::encode($payload, $jwtSecret, 'HS256');

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource($user)
        ];
    }

    public static function generateTempTokenResponse($user) {
        $payload = [
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->name,
            'expiry' => Carbon::now()->addHours(15)->timestamp,
        ];

        $jwtSecret = config('app.jwt_secret');
        if (empty($jwtSecret)) {
            throw new \Exception('JWT_SECRET is not configured. Please check your .env file.');
        }

        $token = JWT::encode($payload, $jwtSecret, 'HS256');

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource($user)
        ];
    }
}
