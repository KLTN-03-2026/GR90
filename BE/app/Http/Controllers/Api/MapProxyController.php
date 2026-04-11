<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapProxyController extends Controller
{
    /**
     * Proxy selected BusMap map resources to avoid browser CORS blocks.
     */
    public function __invoke(Request $request)
    {
        $targetUrl = trim((string) $request->query('url', ''));
        if ($targetUrl === '') {
            return response()->json([
                'message' => 'Missing url query parameter.',
            ], 422);
        }

        if (! filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            return response()->json([
                'message' => 'Invalid url query parameter.',
            ], 422);
        }

        $parsed = parse_url($targetUrl);
        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));
        $host = strtolower((string) ($parsed['host'] ?? ''));

        if ($scheme !== 'https' || ! $this->isAllowedHost($host)) {
            return response()->json([
                'message' => 'Target host is not allowed.',
            ], 403);
        }

        // Vector tile upstream is unstable in local environment; fail fast to protect API workers.
        if (str_starts_with($host, 'bmap-tiles-')) {
            return response('Upstream tile host temporarily disabled.', 503)
                ->header('Cache-Control', 'public, max-age=60');
        }

        try {
            $upstream = Http::withHeaders([
                'Accept' => '*/*',
                'User-Agent' => 'UrbanBusLookupMapProxy/1.0',
            ])->connectTimeout(2)->timeout(4)->retry(0, 0)->get($targetUrl);
        } catch (\Throwable $e) {
            return response('Upstream timeout.', 504)
                ->header('Cache-Control', 'public, max-age=20');
        }

        $response = response($upstream->body(), $upstream->status());

        $contentType = $upstream->header('Content-Type');
        if (is_string($contentType) && $contentType !== '') {
            $response->header('Content-Type', $contentType);
        }

        $cacheControl = $upstream->header('Cache-Control');
        if (is_string($cacheControl) && $cacheControl !== '') {
            $response->header('Cache-Control', $cacheControl);
        } else {
            $response->header('Cache-Control', 'public, max-age=300');
        }

        return $response;
    }

    private function isAllowedHost(string $host): bool
    {
        $allowedHosts = [
            'files.busmap.vn',
            'api-web.busmap.vn',
        ];

        return in_array($host, $allowedHosts, true);
    }
}
