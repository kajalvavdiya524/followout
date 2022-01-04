<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class UpdateLastSeenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->guard('web')->check()) {
            $this->updateLastSeenDate(auth()->guard('web')->user());
        }

        if (auth()->guard('api')->check()) {
            $this->updateLastSeenDate(auth()->guard('api')->user());
        }

        return $next($request);
    }

    /**
     * Update user's last seen date.
     *
     * @param  \App\User  $user
     * @return bool
     */
    private function updateLastSeenDate(User $user)
    {
        $user->last_seen = now();
        $user->save();

        return true;
    }
}
