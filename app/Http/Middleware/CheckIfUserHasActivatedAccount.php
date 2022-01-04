<?php

namespace App\Http\Middleware;

use Closure;
use Route;
use Str;

class CheckIfUserHasActivatedAccount
{
    /**
     * The URIs that should be excluded from verification.
     *
     * @var array
     */
    protected $except = [
        '/register/social',
        '/account_activation',
        '/account_activation/resend',
        '/activate_account',
        '/activate_account/*',
        '/support/contact',
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
        if (auth()->check() && !auth()->user()->isActivated() && !$this->shouldPassThrough($request)) {
            if (session()->has('REDIRECT_TO_CHECKOUT') || session()->has('REDIRECT_TO_CHARGEBEE')) {
                return $next($request);
            }

            if (Str::endsWith(Route::currentRouteAction(), 'OrderController@pay') || Str::endsWith(Route::currentRouteAction(), 'OrderController@payment')) {
                return $next($request);
            }

            session()->reflash();

            return redirect()->action('UsersController@askForAccountActivation');
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
