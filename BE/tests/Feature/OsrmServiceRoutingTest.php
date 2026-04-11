<?php

use App\Services\OsrmService;
use Illuminate\Support\Facades\Http;

it('accepts lng lat associative arrays for OSRM requests', function () {
    config(['services.osrm.base_url' => 'https://router.project-osrm.org']);

    Http::fake([
        'https://router.project-osrm.org/*' => Http::response([
            'routes' => [[
                'geometry' => [
                    'coordinates' => [
                        [108.0, 16.0],
                        [108.01, 16.01],
                        [108.02, 16.02],
                    ],
                ],
            ]],
        ], 200),
    ]);

    $service = new OsrmService;

    $result = $service->routeCoordinates([
        ['lng' => 108.0, 'lat' => 16.0],
        ['lng' => 108.02, 'lat' => 16.02],
    ]);

    expect($result)->not->toBeEmpty()
        ->and(count($result))->toBeGreaterThan(1);

    Http::assertSent(function ($request) {
        $url = $request->url();

        return str_contains($url, '108,16')
            && str_contains($url, '108.02,16.02');
    });
});

it('accepts indexed lng lat pairs', function () {
    config(['services.osrm.base_url' => 'https://router.project-osrm.org']);

    Http::fake([
        'https://router.project-osrm.org/*' => Http::response([
            'routes' => [[
                'geometry' => [
                    'coordinates' => [[108.0, 16.0], [108.05, 16.05]],
                ],
            ]],
        ], 200),
    ]);

    $service = new OsrmService;

    $result = $service->routeCoordinates([[108.0, 16.0], [108.05, 16.05]]);

    expect($result)->not->toBeEmpty();
});
