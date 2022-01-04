<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure     $next
     * @param  string       $role
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $guard = null)
    {
        if (auth()->guard($guard)->guest()) {
            return redirect()->route('login');
        }

        if (auth()->guard($guard)->user()->role !== $role) {
            if ($guard === 'api') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied.',
                ], 403);
            }

            return abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
