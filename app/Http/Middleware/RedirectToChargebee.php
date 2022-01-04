<?php

namespace App\Http\Middleware;

use Closure;
use App\Product;

class RedirectToChargebee
{
    /**
     * The URIs that should be excluded from verification.
     *
     * @var array
     */
    protected $except = [
        '/activate_account',
        '/activate_account/*',
        '/cart/add/*',
        '/chargebee',
        '/chargebee/redirect',
        '/chargebee/handle',
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
        if (auth()->check() && session()->has('REDIRECT_TO_CHARGEBEE') && !$this->shouldPassThrough($request)) {
            if (auth()->user()->subscribedToPro()) {
                session()->forget('REDIRECT_TO_CHARGEBEE');
            }

            session()->reflash();

            $plan = session()->get('REDIRECT_TO_CHARGEBEE');

            if ($plan === 'monthly' || $plan === 'annual') {
                if ($plan === 'annual') {
                    $product = Product::subscriptionYearly()->first();
                } elseif ($plan === 'monthly') {
                    $product = Product::subscriptionMonthly()->first();
                }

                return redirect()->route('cart.add', ['product' => $product->id]);
            }

            return redirect()->route('welcome');
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
