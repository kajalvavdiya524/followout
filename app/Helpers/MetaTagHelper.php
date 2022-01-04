<?php

namespace App\Helpers;

use Route;

class MetaTagHelper
{
    public $title;

    public $description;

    public $routeName;

    public $routes;

    public function __construct()
    {
        // Default values
        $this->title = config('app.name');
        $this->description = 'Create a Followout. Acquire customers. Attract local buyers. Increase foot traffic. Restaurant promotion. Gym promotion. Store promotion.';

        // Current route name
        $this->routeName = Route::currentRouteName() ?? null;

        // All route names with custom meta data
        $this->routes = [
            'login' => [
                'title' => 'Login | '.config('app.name'),
            ],
            'register' => [
                'title' => 'Register | '.config('app.name'),
            ],
            'about' => [
                'title' => 'About '.config('app.name'),
            ],
            'university' => [
                'title' => config('app.name').' University',
            ],
        ];
    }

    public function getTitle()
    {
        return $this->routes[$this->routeName]['title'] ?? $this->title;
    }

    public function getDescription()
    {
        return $this->routes[$this->routeName]['description'] ?? $this->description;
    }
}
