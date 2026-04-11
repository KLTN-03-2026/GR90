<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class OsrmService
{
    private const CACHE_TTL_HOURS = 24;

    private const MAX_WAYPOINTS = 25;

    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.osrm.base_url', 'https://router.project-osrm.org');
    }

    public function routeCoordinates(array $coordinates): array
    {
        if (count($coordinates) < 2) {
            return $coordinates;
        }

        $coordinates = collect($coordinates)
            ->map(fn ($coord) => $this->normalizeCoord($coord))
            ->filter(fn ($coord) => $coord !== null)
            ->values()
            ->all();

        if (count($coordinates) < 2) {
            return [];
        }

        // Chunk large waypoint lists to stay within OSRM limits
        $chunks = collect($coordinates)
            ->chunk(self::MAX_WAYPOINTS)
            ->values();

        $result = [];

        foreach ($chunks as $index => $chunk) {
            $chunkCoords = $chunk->values()->all();

            if ($index > 0 && count($result) > 0) {
                // Drop the first point of subsequent chunks — it's shared with the last result point
                array_shift($chunkCoords);
            }

            $routed = $this->routeChunk($chunkCoords);

            foreach ($routed as $coord) {
                $result[] = $coord;
            }
        }

        return $result;
    }

    private function routeChunk(array $coordinates): array
    {
        $cacheKey = 'osrm_route_'.md5(json_encode($coordinates));

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $coordsStr = collect($coordinates)
            ->map(fn ($coord) => implode(',', $coord))
            ->join(';');

        $timeout = (int) config('services.osrm.timeout', 10);

        try {
            $response = Http::timeout($timeout)
                ->retry(2, 500)
                ->get("{$this->baseUrl}/route/v1/driving/{$coordsStr}", [
                    'overview' => 'full',
                    'geometries' => 'geojson',
                    'steps' => 'false',
                ]);

            if (! $response->ok()) {
                return $this->fallbackStraightLine($coordinates);
            }

            $data = $response->json();

            if (empty($data['routes'][0]['geometry']['coordinates'])) {
                return $this->fallbackStraightLine($coordinates);
            }

            $geometry = collect($data['routes'][0]['geometry']['coordinates'])
                ->map(fn ($coord) => $this->normalizeCoord($coord))
                ->filter(fn ($coord) => $coord !== null)
                ->values()
                ->all();

            Cache::put($cacheKey, $geometry, now()->addHours(self::CACHE_TTL_HOURS));

            return $geometry;
        } catch (Throwable) {
            return $this->fallbackStraightLine($coordinates);
        }
    }

    private function normalizeCoord($coord): ?array
    {
        if (! is_array($coord)) {
            return null;
        }

        if (array_key_exists('lng', $coord) && array_key_exists('lat', $coord)) {
            $lng = is_numeric($coord['lng']) ? (float) $coord['lng'] : null;
            $lat = is_numeric($coord['lat']) ? (float) $coord['lat'] : null;

            if ($lng !== null && $lat !== null) {
                return [$lng, $lat];
            }

            return null;
        }

        if (count($coord) >= 2) {
            $lng = is_numeric($coord[0]) ? (float) $coord[0] : null;
            $lat = is_numeric($coord[1]) ? (float) $coord[1] : null;

            if ($lng !== null && $lat !== null) {
                return [$lng, $lat];
            }
        }

        return null;
    }

    private function fallbackStraightLine(array $coordinates): array
    {
        return collect($coordinates)
            ->map(fn ($coord) => $this->normalizeCoord($coord))
            ->filter(fn ($coord) => $coord !== null)
            ->values()
            ->all();
    }
}
