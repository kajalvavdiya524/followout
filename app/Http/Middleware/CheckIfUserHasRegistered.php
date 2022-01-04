<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfUserHasRegistered
{
    /**
     * The URIs that should be excluded from verification.
     *
     * @var array
     */
    protected $except = [
        '/register/social',
        '/logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->isUnregistered() && !$this->shouldPassThrough($request)) {
            session()->reflash();
            return redirect()->route('register.social');
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
