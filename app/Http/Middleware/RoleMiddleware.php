<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->authUser;

        if (!empty($roles)) {
            $role = $user->role;
            if (!$role || (RoleName::SuperAdmin !== $role->name && !in_array($role->name->value, $roles))) {
                return response()->json(['error' => 'Unauthorized Role'], 403);
            }
        }

        return $next($request);
    }
}
