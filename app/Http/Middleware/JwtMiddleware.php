<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => __('messages.missing_or_invalid_token')], 401);
        }

        $jwt = substr($authHeader, 7);

        try {
            $token = JWT::decode($jwt, new Key(config('app.jwt_secret'), 'HS256'));
            $token = (array) $token;

            if ($token['expiry'] != null && Carbon::now()->timestamp > $token['expiry']) {
                return response()->json(['message' => __('messages.expired_token')], 401);
            }

            $request->merge(['authUserId' => $token['sub']]);
        } catch (\Exception $e) {
            return response()->json(['message' => __('messages.invalid_token')], 401);
        }

        return $next($request);
    }

}
