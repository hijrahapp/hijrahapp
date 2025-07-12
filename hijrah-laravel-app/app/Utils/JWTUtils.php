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

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

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

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource($user)
        ];
    }
}
