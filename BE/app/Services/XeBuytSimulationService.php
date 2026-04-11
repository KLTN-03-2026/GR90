<?php

namespace App\Services;

use App\Models\LoTrinhTuyen;
use App\Models\TramXe;
use App\Models\XeBuyt;
use Illuminate\Support\Collection;

class XeBuytSimulationService
{
    private const CACHE_TTL_SECONDS = 10;

    public function getActiveBusesByRoute(int $tuyenXeId): array
    {
        $cacheKey = "active_buses_route_{$tuyenXeId}";

        return cache()->remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($tuyenXeId) {
            return $this->simulateActiveBusesForRoute($tuyenXeId);
        });
    }

    public function getActiveBusesByStop(int $tramXeId): array
    {
        $cacheKey = "active_buses_stop_{$tramXeId}";

        return cache()->remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($tramXeId) {
            return $this->simulateActiveBusesForStop($tramXeId);
        });
    }

    public function getBusesByRoute(int $tuyenXeId): array
    {
        $xeBuyts = XeBuyt::query()
            ->with(['tuyenXe'])
            ->where('tuyen_xe_id', $tuyenXeId)
            ->where('trang_thai', 'dang_hoat_dong')
            ->get();

        $loTrinhs = LoTrinhTuyen::query()
            ->where('tuyen_xe_id', $tuyenXeId)
            ->with(['chiTietLoTrinhs.tramXe'])
            ->orderBy('id')
            ->get();

        if ($loTrinhs->isEmpty()) {
            return [];
        }

        $routeCoords = $this->extractRouteCoordinates($loTrinhs);
        $tuyenXe = $xeBuyts->first()?->tuyenXe;

        return $xeBuyts->map(function (XeBuyt $xe) use ($routeCoords, $loTrinhs, $tuyenXe) {
            $progress = $this->generateProgress($xe->id);
            $coords = $this->interpolateAlongRoute($routeCoords, $progress);
            $direction = $progress < 0.5 ? 'di' : 've';
            $loTrinh = $loTrinhs->firstWhere('chieu', $direction)
                ?? $loTrinhs->first();

            return [
                'id' => $xe->id,
                'bien_so' => $xe->bien_so,
                'ten_xe' => $xe->ten_xe,
                'loai_xe' => $xe->loai_xe,
                'so_cho' => $xe->so_cho,
                'trang_thai' => $xe->trang_thai,
                'tuyen_xe_id' => $xe->tuyen_xe_id,
                'ma_tuyen' => $tuyenXe?->ma_tuyen,
                'ten_tuyen' => $tuyenXe?->ten_tuyen,
                'chieu' => $direction,
                'vi_tri' => [
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                ],
                'toc_do_kmh' => fake()->randomFloat(1, 15, 40),
                'thoi_gian_cap_nhat' => now()->toIso8601String(),
            ];
        })->values()->all();
    }

    public function getScheduleByRoute(int $tuyenXeId): array
    {
        $tuyenXe = \App\Models\TuyenXe::query()
            ->with(['loaiTuyen', 'trangThaiTuyen', 'donViVanHanh'])
            ->findOrFail($tuyenXeId);

        $schedule = [];

        if ($tuyenXe->thoi_gian_bat_dau_hoat_dong && $tuyenXe->thoi_gian_ket_thuc_hoat_dong && $tuyenXe->tan_suat_phut) {
            $startTime = $tuyenXe->thoi_gian_bat_dau_hoat_dong;
            $endTime = $tuyenXe->thoi_gian_ket_thuc_hoat_dong;
            $interval = (int) $tuyenXe->tan_suat_phut;

            $current = $startTime->copy();
            $direction = 'di';

            while ($current->lessThanOrEqualTo($endTime)) {
                $schedule[] = [
                    'gio' => $current->format('H:i'),
                    'chieu' => $direction === 'di' ? 'Chiều đi' : 'Chiều về',
                    'chieu_key' => $direction,
                    'tuyen_xe_id' => $tuyenXe->id,
                    'ten_tuyen' => $tuyenXe->ten_tuyen,
                ];

                $current->addMinutes($interval);

                if ($current->greaterThan($endTime)) {
                    break;
                }

                if ($direction === 'di') {
                    $direction = 've';
                } else {
                    $direction = 'di';
                }
            }
        }

        return [
            'tuyen_xe' => [
                'id' => $tuyenXe->id,
                'ma_tuyen' => $tuyenXe->ma_tuyen,
                'ten_tuyen' => $tuyenXe->ten_tuyen,
                'loai_tuyen' => $tuyenXe->loaiTuyen?->ten_loai_tuyen,
            ],
            'thoi_gian_bat_dau' => optional($tuyenXe->thoi_gian_bat_dau_hoat_dong)->format('H:i'),
            'thoi_gian_ket_thuc' => optional($tuyenXe->thoi_gian_ket_thuc_hoat_dong)->format('H:i'),
            'tan_suat_phut' => $tuyenXe->tan_suat_phut,
            'cu_ly_km' => $tuyenXe->cu_ly_km,
            'don_vi_van_hanh' => $tuyenXe->donViVanHanh?->ten_don_vi,
            'lich_xuat_benh' => $schedule,
        ];
    }

    private function simulateActiveBusesForRoute(int $tuyenXeId): array
    {
        $xeBuyts = XeBuyt::query()
            ->where('tuyen_xe_id', $tuyenXeId)
            ->where('trang_thai', 'dang_hoat_dong')
            ->with(['tuyenXe'])
            ->get();

        $loTrinhs = LoTrinhTuyen::query()
            ->where('tuyen_xe_id', $tuyenXeId)
            ->with(['chiTietLoTrinhs.tramXe'])
            ->orderBy('id')
            ->get();

        if ($loTrinhs->isEmpty()) {
            return [];
        }

        $routeCoords = $this->extractRouteCoordinates($loTrinhs);
        $tuyenXe = $xeBuyts->first()?->tuyenXe;

        return $xeBuyts->map(function (XeBuyt $xe) use ($routeCoords, $loTrinhs, $tuyenXe) {
            $progress = $this->generateProgress($xe->id);
            $coords = $this->interpolateAlongRoute($routeCoords, $progress);
            $direction = $progress < 0.5 ? 'di' : 've';
            $loTrinh = $loTrinhs->firstWhere('chieu', $direction) ?? $loTrinhs->first();
            $nearestStop = $this->findNearestStop($coords, $loTrinh);

            return [
                'id' => $xe->id,
                'bien_so' => $xe->bien_so,
                'ten_xe' => $xe->ten_xe,
                'loai_xe' => $xe->loai_xe,
                'so_cho' => $xe->so_cho,
                'chieu' => $direction,
                'chieu_label' => $direction === 'di' ? 'Chiều đi' : 'Chiều về',
                'vi_tri' => [
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                ],
                'tram_gan_nhat' => $nearestStop,
                'toc_do_kmh' => fake()->randomFloat(1, 15, 40),
                'thoi_gian_den_tram_gan_km' => fake()->randomFloat(1, 1, 8),
                'thoi_gian_cap_nhat' => now()->toIso8601String(),
            ];
        })->values()->all();
    }

    private function simulateActiveBusesForStop(int $tramXeId): array
    {
        $tramXe = TramXe::query()->findOrFail($tramXeId);
        $chiTietLoTrinhs = \App\Models\ChiTietLoTrinh::query()
            ->where('tram_xe_id', $tramXeId)
            ->with(['loTrinhTuyen.tuyenXe'])
            ->get();

        $tuyenXeIds = $chiTietLoTrinhs->pluck('loTrinhTuyen.tuyen_xe_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($tuyenXeIds)) {
            return [];
        }

        $xeBuyts = XeBuyt::query()
            ->whereIn('tuyen_xe_id', $tuyenXeIds)
            ->where('trang_thai', 'dang_hoat_dong')
            ->with(['tuyenXe.loTrinhTuyens.chiTietLoTrinhs.tramXe'])
            ->get();

        $stopLat = (float) $tramXe->vi_do;
        $stopLng = (float) $tramXe->kinh_do;

        return $xeBuyts->map(function (XeBuyt $xe) use ($stopLat, $stopLng, $tramXeId, $chiTietLoTrinhs) {
            $progress = $this->generateProgress($xe->id + 1000);
            $routeCoords = $this->extractRouteCoordinates(
                $xe->tuyenXe->loTrinhTuyens ?? collect([])
            );
            $coords = $this->interpolateAlongRoute($routeCoords, $progress);

            $distanceToStop = $this->haversineDistance(
                $coords['lat'], $coords['lng'],
                $stopLat, $stopLng
            );

            $etaMinutes = $distanceToStop > 0.1
                ? fake()->numberBetween(1, 15)
                : 0;

            $incoming = fake()->boolean(70);
            $sequenceNumber = fake()->numberBetween(1, 5);

            return [
                'id' => $xe->id,
                'bien_so' => $xe->bien_so,
                'ten_xe' => $xe->ten_xe,
                'loai_xe' => $xe->loai_xe,
                'so_cho' => $xe->so_cho,
                'tuyen_xe_id' => $xe->tuyen_xe_id,
                'ma_tuyen' => $xe->tuyenXe?->ma_tuyen,
                'ten_tuyen' => $xe->tuyenXe?->ten_tuyen,
                'vi_tri' => [
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                ],
                'khoang_cach_km' => round($distanceToStop, 1),
                'thoi_gian_den_km_phut' => $etaMinutes,
                'dang_tien_ve' => $incoming,
                'so_chuyen' => fake()->numberBetween(1, 10),
                'thoi_gian_cap_nhat' => now()->toIso8601String(),
            ];
        })->filter(function ($xe) {
            return $xe['khoang_cach_km'] <= 15;
        })
        ->sortBy('thoi_gian_den_km_phut')
        ->take(10)
        ->values()
        ->all();
    }

    private function extractRouteCoordinates(Collection $loTrinhs): array
    {
        $coords = [];

        foreach ($loTrinhs as $loTrinh) {
            foreach ($loTrinh->chiTietLoTrinhs ?? [] as $chiTiet) {
                $tram = $chiTiet->tramXe;
                if ($tram && is_numeric($tram->vi_do) && is_numeric($tram->kinh_do)) {
                    $coords[] = [
                        'lat' => (float) $tram->vi_do,
                        'lng' => (float) $tram->kinh_do,
                    ];
                }
            }
        }

        return $coords;
    }

    private function interpolateAlongRoute(array $coords, float $progress): array
    {
        if (count($coords) < 2) {
            return $coords[0] ?? ['lat' => 16.0544, 'lng' => 108.2022];
        }

        $totalSegments = count($coords) - 1;
        $scaledProgress = $progress * $totalSegments;
        $segmentIndex = min((int) floor($scaledProgress), $totalSegments - 1);
        $segmentFraction = $scaledProgress - $segmentIndex;

        $start = $coords[$segmentIndex];
        $end = $coords[$segmentIndex + 1];

        return [
            'lat' => $start['lat'] + ($end['lat'] - $start['lat']) * $segmentFraction,
            'lng' => $start['lng'] + ($end['lng'] - $start['lng']) * $segmentFraction,
        ];
    }

    private function generateProgress(int $seed): float
    {
        srand($seed + (int) floor(time() / 10));
        $rand = rand(0, 100) / 100;

        return $rand;
    }

    private function findNearestStop(array $currentCoords, ?LoTrinhTuyen $loTrinh): ?array
    {
        if (! $loTrinh) {
            return null;
        }

        $minDist = PHP_FLOAT_MAX;
        $nearest = null;

        foreach ($loTrinh->chiTietLoTrinhs ?? [] as $chiTiet) {
            $tram = $chiTiet->tramXe;
            if (! $tram || ! is_numeric($tram->vi_do) || ! is_numeric($tram->kinh_do)) {
                continue;
            }

            $dist = $this->haversineDistance(
                $currentCoords['lat'], $currentCoords['lng'],
                (float) $tram->vi_do, (float) $tram->kinh_do
            );

            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = [
                    'id' => $tram->id,
                    'ten_tram' => $tram->ten_tram,
                    'khoang_cach_km' => round($dist, 1),
                    'thoi_gian_du_kien_phut' => $dist > 0 ? round($dist / 0.5 * 60) : 0,
                ];
            }
        }

        return $nearest;
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * asin(sqrt($a));

        return $earthRadius * $c;
    }
}
