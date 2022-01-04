<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfProfileMissingInfo
{
    /**
     * The URIs that should be excluded from verification.
     *
     * @var array
     */
    protected $except = [
        '/cart/*',
        '/chargebee',
        '/chargebee/*',
        '/checkout',
        '/payments/*',
        '/users/*',
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
        if (auth()->guest() || session()->get('REDIRECT_TO_CHARGEBEE') || session()->get('REDIRECT_TO_CHECKOUT')) {
            return $next($request);
        }

        if (auth()->user()->isFollowhost() && auth()->user()->isMissingProfileInfo() && auth()->user()->isActivated() && auth()->user()->isRegistered() && !$this->shouldPassThrough($request)) {
            session()->flash('toastr.info', 'Please fill up the rest of your profile.');
            return redirect()->route('users.edit', ['user' => auth()->user()->id]);
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
