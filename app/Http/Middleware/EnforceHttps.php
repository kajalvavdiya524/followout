<?php

namespace App\Http\Middleware;

use Closure;

class EnforceHttps
{
    public function handle($request, Closure $next, $middlewareGroup = 'web')
    {
        if (!$request->secure() && env('SECURE_CONNECTION', false)) {
            if ($middlewareGroup === 'api') {
                return response()->json(['message' => 'Only secure requests via HTTPS are allowed.'], 501);
            } else {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }
}
