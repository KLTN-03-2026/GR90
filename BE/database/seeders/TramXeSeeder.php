<?php

namespace Database\Seeders;

use App\Models\TramXe;
use Database\Seeders\Concerns\InteractsWithBusMapApi;
use Illuminate\Database\Seeder;

class TramXeSeeder extends Seeder
{
    use InteractsWithBusMapApi;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = $this->busMapRouteList('dn');
        $uniqueStations = [];

        foreach ($routes as $route) {
            $routeId = $route['routeId'] ?? null;
            if (! $routeId) {
                continue;
            }

            $detail = $this->busMapRouteDetail((int) $routeId, 'dn');
            $stations = is_array($detail['stations'] ?? null) ? $detail['stations'] : [];

            foreach ($stations as $station) {
                $stationId = (int) ($station['stationId'] ?? 0);
                if ($stationId <= 0) {
                    continue;
                }

                $uniqueStations[$stationId] = [
                    'stationId' => $stationId,
                    'stationName' => $this->busMapNormalizeText($station['stationName'] ?? ''),
                    'stationAddress' => $this->busMapNormalizeText($station['stationAddress'] ?? ''),
                    'lat' => is_numeric($station['lat'] ?? null) ? (float) $station['lat'] : null,
                    'lng' => is_numeric($station['lng'] ?? null) ? (float) $station['lng'] : null,
                ];
            }
        }

        foreach ($uniqueStations as $station) {
            $maTram = 'DN_TRAM_' . $station['stationId'];
            $diaChi = $station['stationAddress'] !== '' ? $station['stationAddress'] : null;

            TramXe::query()->updateOrCreate(
                ['ma_tram' => $maTram],
                [
                    'ten_tram' => $station['stationName'] !== '' ? $station['stationName'] : 'Trạm ' . $station['stationId'],
                    'dia_chi' => $diaChi,
                    'khu_vuc' => 'Đà Nẵng',
                    'vi_do' => $station['lat'],
                    'kinh_do' => $station['lng'],
                ]
            );
        }
    }
}
