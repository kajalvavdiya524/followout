<?php

namespace App\Http\Middleware;

use Closure;
use App\Product;

class RedirectToCheckout
{
    /**
     * The URIs that should be excluded from verification.
     *
     * @var array
     */
    protected $except = [
        '/activate_account',
        '/activate_account/*',
        '/checkout',
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
        if (auth()->check() && session()->has('REDIRECT_TO_CHECKOUT') && !$this->shouldPassThrough($request)) {
            session()->reflash();

            $user = auth()->user();

            $plan = session()->get('REDIRECT_TO_CHECKOUT');

            if ($plan === 'basic') {
                $product = Product::subscriptionBasic()->first();

                // Initialize empty cart
                $cart = collect([]);

                // Push subscription product to cart
                $cart->push($product->id);

                // Save user's cart
                $user->cart = $cart->toArray();
                $user->save();

                return redirect()->route('checkout');
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
