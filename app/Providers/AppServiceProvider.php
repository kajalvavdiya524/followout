<?php

namespace App\Providers;

use URL;
use View;
use Validator;
use App\Product;
use App\FollowoutCategory;
use App\SalesRepresentative;
use GooglePlacesHelper;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Jenssegers\Mongodb\Eloquent\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapThree();

        Blade::withoutDoubleEncoding();

        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        Validator::extend('phone_number', function($attribute, $value, $parameters, $validator) {
            // Match: "+", "0-9", " ", "-"
            // Rule: Last character is 0-9
            return preg_match("/^\+?[0-9 -]+[0-9]$/", $value);
        });

        Validator::extend('phone_number_int', function($attribute, $value, $parameters, $validator) {
            // Match: "+", "0-9", " ", "-"
            // Rule: First character is "+"
            // Rule: Last character is 0-9
            return preg_match("/^\+[0-9 -]+[0-9]$/", $value);
        });

        Validator::extend('zip_code', function($attribute, $value, $parameters, $validator) {
            // Match: "0-9", "-", "a-z", "A-Z"
            return preg_match('/^([0-9a-zA-Z]{5,6})(\-[0-9a-zA-Z]{3,5})?$/', $value);
        });

        Validator::extend('float', function ($attribute, $value, $parameters, $validator) {
            return $value == (string) (float) $value;
        });

        Validator::extend('lat', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $value);
        });

        Validator::extend('lng', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $value);
        });

        Validator::extend('sales_rep_code_exists', function ($attribute, $value, $parameters, $validator) {
            return SalesRepresentative::where('code', $value)->orWhere('promo_code', $value)->exists();
        });

        Validator::extend('google_places_type', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, GooglePlacesHelper::getFilterablePlaceTypes());
        });

        View::composer('includes.header', function($view) {
            $followoutCategories = FollowoutCategory::orderBy('name', 'ASC')->get();

            $view->with('followoutCategories', $followoutCategories);
        });

        if (env('SECURE_CONNECTION', false)) {
            URL::forceScheme('https');
        }

        $appData = [
            'basicSubscription' => Product::subscriptionBasic()->first(),
            'monthlySubscription' => Product::subscriptionMonthly()->first(),
            'yearlySubscription' => Product::subscriptionYearly()->first(),
        ];

        View::share('appData', $appData);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Builder::macro('getName', function() {
            return 'mongodb';
        });
    }
}
