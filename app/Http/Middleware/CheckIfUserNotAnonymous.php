<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfUserNotAnonymous
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (auth()->guard($guard)->check()) {
            if (auth()->guard($guard)->user()->isAnonymous()) {
                if ($guard === 'api') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Access denied. Please log in.',
                    ], 403);
                }

                return abort(403, 'Access denied. Please log in.');
            }
        }

        return $next($request);
    }
}
