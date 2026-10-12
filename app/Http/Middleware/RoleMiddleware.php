<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        string $role
    ): Response {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if (!$user->roles()->where('name', $role)->exists()) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
