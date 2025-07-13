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

        $token = JWT::encode($payload, config('app.jwt_secret'), 'HS256');

        return self::generateResponse($token, $user);
    }

    public static function generateTempTokenResponse($user) {
        $payload = [
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->name,
            'expiry' => Carbon::now()->addHours(15)->timestamp,
        ];

        $token = JWT::encode($payload, config('app.jwt_secret'), 'HS256');


        return self::generateResponse($token, $user);
    }

    private static function generateResponse(string $token, $user): array
    {
        $response = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource($user)
        ];

        return $response;
    }
}
