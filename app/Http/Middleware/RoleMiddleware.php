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

        $role = $user->role;
        if(!$role) {
            return response()->json(['error' => 'Unauthorized Role'], 403);
        }
        if(RoleName::SuperAdmin !== $role->name && (empty($roles) || !in_array($role->name->value, $roles))) {
            return response()->json(['error' => 'Unauthorized Role'], 403);
        }

        return $next($request);
    }
}
