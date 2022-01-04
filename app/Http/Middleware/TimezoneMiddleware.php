<?php

namespace App\Http\Middleware;

use Closure;
use Validator;

class TimezoneMiddleware
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
        $cookieTZ = $this->getCookieTZ();

        if ($cookieTZ) {
            session()->put('TIMEZONE', $cookieTZ);
        } else {
            session()->put('TIMEZONE', 'UTC');
        }

        return $next($request);
    }

    private function getCookieTZ()
    {
        if (!isset($_COOKIE['timezone'])) {
            return null;
        }

        $validator = Validator::make($_COOKIE, [
            'timezone' => 'required|timezone',
        ]);

        if ($validator->fails()) {
            return null;
        }

        return $_COOKIE['timezone'];
    }
}
