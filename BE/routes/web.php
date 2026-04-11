<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api-docs', function () {
    $apiRoutes = collect(Route::getRoutes())
        ->filter(fn ($route) => str_starts_with($route->uri(), 'api/admin/'))
        ->map(function ($route) {
            $methods = collect($route->methods())
                ->reject(fn (string $method) => $method === 'HEAD')
                ->values()
                ->all();

            return [
                'uri' => '/' . $route->uri(),
                'methods' => $methods,
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        })
        ->values();

    return view('api-docs', [
        'apiRoutes' => $apiRoutes,
        'baseUrl' => url('/'),
    ]);
});
