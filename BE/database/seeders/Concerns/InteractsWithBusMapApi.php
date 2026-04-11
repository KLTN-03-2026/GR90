<?php

namespace Database\Seeders\Concerns;

use RuntimeException;

trait InteractsWithBusMapApi
{
    private string $busMapApiBase = 'https://api-web.busmap.vn/web/public';

    private string $busMapDecryptKey = 'BuSm@p2ol9#K3(y)th3BUSiNiTv3ct0r';

    private string $busMapDecryptIv = 'th3BUSiNiTv3ct0r';

    protected function busMapRouteList(string $regionCode = 'dn'): array
    {
        $url = sprintf('%s/route/list?regionCode=%s', $this->busMapApiBase, $regionCode);
        $plain = $this->busMapDecryptResponse($url);

        return $this->extractJsonArray($plain);
    }

    protected function busMapRouteDetail(int|string $routeId, string $regionCode = 'dn'): array
    {
        $url = sprintf('%s/route/detail?routeId=%s&regionCode=%s', $this->busMapApiBase, $routeId, $regionCode);
        $plain = $this->busMapDecryptResponse($url);

        return $this->extractJsonObject($plain);
    }

    protected function busMapNormalizeText(?string $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function busMapDecryptResponse(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 25,
                'header' => "Accept: application/json\r\n",
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            throw new RuntimeException('Không thể tải dữ liệu BusMap từ URL: ' . $url);
        }

        $hexPayload = trim($raw, "\"\r\n ");
        $cipher = hex2bin($hexPayload);

        if ($cipher === false) {
            throw new RuntimeException('Payload BusMap không đúng định dạng hex. URL: ' . $url);
        }

        $plain = openssl_decrypt(
            $cipher,
            'AES-256-CBC',
            $this->busMapDecryptKey,
            OPENSSL_RAW_DATA,
            $this->busMapDecryptIv
        );

        if (! is_string($plain) || $plain === '') {
            throw new RuntimeException('Giải mã dữ liệu BusMap thất bại. URL: ' . $url);
        }

        return $plain;
    }

    private function extractJsonArray(string $plain): array
    {
        $start = strpos($plain, '[');
        $end = strrpos($plain, ']');

        if ($start === false || $end === false || $end <= $start) {
            return [];
        }

        $payload = substr($plain, $start, $end - $start + 1);
        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function extractJsonObject(string $plain): array
    {
        $start = strpos($plain, '{');
        $end = strrpos($plain, '}');

        if ($start === false || $end === false || $end <= $start) {
            return [];
        }

        $payload = substr($plain, $start, $end - $start + 1);
        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }
}
