<?php

namespace App\Http\Middleware;

use App\Http\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function __construct(private UserRepository $userRepo) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->userRepo->findById($request->authUserId);
        if (!$user) {
            return response()->json(['message' => 'Invalid user'], 401);
        }
        if (!$user->active) {
            return response()->json(['message' => 'Inactive user'], 401);
        }

        $request->merge(['authUser' => $user]);

        return $next($request);
    }
}
