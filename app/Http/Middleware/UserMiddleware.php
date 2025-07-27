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
            return response()->json(['message' => __('messages.user_not_found')], 404);
        }
        if (!$user->active) {
            return response()->json(['message' => __('messages.inactive_user')], 401);
        }

        $request->merge(['authUser' => $user]);

        return $next($request);
    }

    public function fetchAndValidateUser(int $userId)
    {
        $user = $this->userRepo->findById($userId);

        if (!$user) {
            return ['message' => __('messages.user_not_found')];
        }
        if (!$user->active) {
            return ['message' => __('messages.inactive_user')];
        }

        return $user;
    }
}
