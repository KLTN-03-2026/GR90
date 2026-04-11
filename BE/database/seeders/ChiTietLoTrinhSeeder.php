<?php

namespace Database\Seeders;

use App\Models\ChiTietLoTrinh;
use App\Models\LoTrinhTuyen;
use App\Models\TramXe;
use App\Models\TuyenXe;
use Database\Seeders\Concerns\InteractsWithBusMapApi;
use Illuminate\Database\Seeder;

class ChiTietLoTrinhSeeder extends Seeder
{
    use InteractsWithBusMapApi;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = $this->busMapRouteList('dn');
        $tramByMa = TramXe::query()->pluck('id', 'ma_tram');

        foreach ($routes as $route) {
            $routeId = (int) ($route['routeId'] ?? 0);
            if ($routeId <= 0) {
                continue;
            }

            $maTuyen = $this->formatMaTuyen((string) ($route['routeNo'] ?? ''), $routeId);
            $tuyenXe = TuyenXe::query()->where('ma_tuyen', $maTuyen)->first();

            if (! $tuyenXe) {
                continue;
            }

            $detail = $this->busMapRouteDetail($routeId, 'dn');
            $stations = is_array($detail['stations'] ?? null) ? $detail['stations'] : [];
            if ($stations === []) {
                continue;
            }

            $grouped = $this->groupStationsByDirection($stations);

            $this->syncDirectionStops($tuyenXe->id, 'di', $grouped['di'] ?? [], $tramByMa);
            $this->syncDirectionStops($tuyenXe->id, 've', $grouped['ve'] ?? [], $tramByMa);
        }
    }

    private function groupStationsByDirection(array $stations): array
    {
        $result = [
            'di' => [],
            've' => [],
        ];

        foreach ($stations as $station) {
            $direction = ((int) ($station['stationDirection'] ?? 0) === 1) ? 've' : 'di';
            $result[$direction][] = $station;
        }

        usort($result['di'], fn ($a, $b) => (int) ($a['stationOrder'] ?? 0) <=> (int) ($b['stationOrder'] ?? 0));
        usort($result['ve'], fn ($a, $b) => (int) ($a['stationOrder'] ?? 0) <=> (int) ($b['stationOrder'] ?? 0));

        return $result;
    }

    private function syncDirectionStops(int $tuyenXeId, string $chieu, array $stations, $tramByMa): void
    {
        if ($stations === []) {
            return;
        }

        $firstName = trim((string) ($stations[0]['stationName'] ?? ''));
        $lastName = trim((string) ($stations[count($stations) - 1]['stationName'] ?? ''));

        $loTrinh = LoTrinhTuyen::query()->updateOrCreate(
            [
                'tuyen_xe_id' => $tuyenXeId,
                'chieu' => $chieu,
            ],
            [
                'mo_ta_lo_trinh' => $this->buildDescription($firstName, $lastName),
            ]
        );

        $validOrders = [];

        foreach ($stations as $index => $station) {
            $thuTu = $index + 1;
            $validOrders[] = $thuTu;

            $stationId = (int) ($station['stationId'] ?? 0);
            $maTram = $stationId > 0 ? ('DN_TRAM_' . $stationId) : null;
            $tramXeId = $maTram ? ($tramByMa[$maTram] ?? null) : null;

            ChiTietLoTrinh::query()->updateOrCreate(
                [
                    'lo_trinh_tuyen_id' => $loTrinh->id,
                    'thu_tu_dung' => $thuTu,
                ],
                [
                    'tram_xe_id' => $tramXeId,
                    'ten_diem_di_qua' => trim((string) ($station['stationName'] ?? '')),
                    'thoi_gian_dung_du_kien_giay' => null,
                    'khoang_cach_tu_diem_truoc_met' => null,
                ]
            );
        }

        ChiTietLoTrinh::query()
            ->where('lo_trinh_tuyen_id', $loTrinh->id)
            ->whereNotIn('thu_tu_dung', $validOrders)
            ->delete();
    }

    private function buildDescription(string $firstName, string $lastName): string
    {
        $first = $firstName !== '' ? $firstName : 'Điểm đầu';
        $last = $lastName !== '' ? $lastName : 'Điểm cuối';

        return $first . ' -> ' . $last;
    }

    private function formatMaTuyen(string $routeNo, int $routeId): string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($routeNo)));
        if ($clean === '') {
            return 'DNR' . $routeId;
        }

        return 'DN' . $clean;
    }
}
