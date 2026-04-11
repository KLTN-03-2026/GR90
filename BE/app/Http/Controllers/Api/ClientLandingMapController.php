<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChiTietLoTrinh;
use App\Models\TramXe;
use App\Models\TuyenXe;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ClientLandingMapController extends Controller
{
    use ApiResponseTrait;

    private const DEFAULT_LAT = 16.0544;
    private const DEFAULT_LNG = 108.2022;

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $lat = (float) ($validated['lat'] ?? self::DEFAULT_LAT);
        $lng = (float) ($validated['lng'] ?? self::DEFAULT_LNG);
        $limit = (int) ($validated['limit'] ?? 3);
        $usedDefaultLocation = ! isset($validated['lat'], $validated['lng']);

        $distanceFormula = '(6371000 * acos(cos(radians(?)) * cos(radians(vi_do)) * cos(radians(kinh_do) - radians(?)) + sin(radians(?)) * sin(radians(vi_do))))';

        $nearbyStops = TramXe::query()
            ->select(['id', 'ma_tram', 'ten_tram', 'dia_chi', 'vi_do', 'kinh_do'])
            ->selectRaw("{$distanceFormula} as distance_m", [$lat, $lng, $lat])
            ->whereNotNull('vi_do')
            ->whereNotNull('kinh_do')
            ->orderBy('distance_m')
            ->limit(30)
            ->get();

        if ($nearbyStops->isEmpty()) {
            return $this->dataResponse([
                'requested_location' => [
                    'lat' => $lat,
                    'lng' => $lng,
                    'used_default_location' => $usedDefaultLocation,
                ],
                'nearest_stop' => null,
                'primary_route' => null,
                'nearby_routes' => [],
            ], 'Không có dữ liệu tuyến gần vị trí hiện tại.');
        }

        $stopDistanceMap = $nearbyStops->keyBy('id');
        $routeCandidates = ChiTietLoTrinh::query()
            ->with([
                'loTrinhTuyen.tuyenXe.loaiTuyen:id,ten_loai_tuyen',
                'loTrinhTuyen.tuyenXe.trangThaiTuyen:id,ten_trang_thai',
            ])
            ->whereIn('tram_xe_id', $nearbyStops->pluck('id'))
            ->get();

        $nearbyRoutes = $routeCandidates
            ->reduce(function ($carry, ChiTietLoTrinh $detail) use ($stopDistanceMap) {
                $route = $detail->loTrinhTuyen?->tuyenXe;
                $stop = $stopDistanceMap->get($detail->tram_xe_id);

                if (! $route || ! $stop) {
                    return $carry;
                }

                $currentDistance = (float) $stop->distance_m;
                $routeId = (int) $route->id;

                if (! isset($carry[$routeId]) || $currentDistance < $carry[$routeId]['distance_m']) {
                    $carry[$routeId] = [
                        'id' => $routeId,
                        'ma_tuyen' => $route->ma_tuyen,
                        'ten_tuyen' => $route->ten_tuyen,
                        'diem_dau' => $route->diem_dau,
                        'diem_cuoi' => $route->diem_cuoi,
                        'loai_tuyen' => $route->loaiTuyen?->ten_loai_tuyen,
                        'trang_thai' => $route->trangThaiTuyen?->ten_trang_thai,
                        'distance_m' => round($currentDistance, 1),
                        'nearest_stop' => [
                            'id' => (int) $stop->id,
                            'ma_tram' => $stop->ma_tram,
                            'ten_tram' => $stop->ten_tram,
                            'dia_chi' => $stop->dia_chi,
                            'vi_do' => (float) $stop->vi_do,
                            'kinh_do' => (float) $stop->kinh_do,
                            'distance_m' => round($currentDistance, 1),
                        ],
                    ];
                }

                return $carry;
            }, []);

        $nearbyRoutes = collect($nearbyRoutes)
            ->sortBy('distance_m')
            ->take($limit)
            ->values();

        $primaryRouteId = (int) ($nearbyRoutes->first()['id'] ?? 0);
        $primaryRoute = $primaryRouteId > 0
            ? TuyenXe::query()
                ->with([
                    'loaiTuyen:id,ten_loai_tuyen',
                    'trangThaiTuyen:id,ten_trang_thai',
                    'loTrinhTuyens' => function ($query) {
                        $query->with([
                            'chiTietLoTrinhs' => function ($chiTietQuery) {
                                $chiTietQuery->with('tramXe:id,ma_tram,ten_tram,dia_chi,vi_do,kinh_do')
                                    ->orderBy('thu_tu_dung');
                            },
                        ])->orderByRaw("case when chieu = 'di' then 0 else 1 end");
                    },
                ])
                ->find($primaryRouteId)
            : null;

        return $this->dataResponse([
            'requested_location' => [
                'lat' => $lat,
                'lng' => $lng,
                'used_default_location' => $usedDefaultLocation,
            ],
            'nearest_stop' => $this->transformNearestStop($nearbyStops->first()),
            'primary_route' => $primaryRoute ? $this->transformRoute($primaryRoute, $nearbyRoutes->first()) : null,
            'nearby_routes' => $nearbyRoutes,
        ], 'Lấy tuyến gần vị trí hiện tại thành công.');
    }

    private function transformNearestStop(?TramXe $stop): ?array
    {
        if (! $stop) {
            return null;
        }

        return [
            'id' => (int) $stop->id,
            'ma_tram' => $stop->ma_tram,
            'ten_tram' => $stop->ten_tram,
            'dia_chi' => $stop->dia_chi,
            'vi_do' => (float) $stop->vi_do,
            'kinh_do' => (float) $stop->kinh_do,
            'distance_m' => round((float) $stop->distance_m, 1),
        ];
    }

    private function transformRoute(TuyenXe $route, ?array $distanceMeta): array
    {
        return [
            'id' => (int) $route->id,
            'ma_tuyen' => $route->ma_tuyen,
            'ten_tuyen' => $route->ten_tuyen,
            'diem_dau' => $route->diem_dau,
            'diem_cuoi' => $route->diem_cuoi,
            'loai_tuyen' => $route->loaiTuyen?->ten_loai_tuyen,
            'trang_thai' => $route->trangThaiTuyen?->ten_trang_thai,
            'distance_m' => $distanceMeta['distance_m'] ?? null,
            'nearest_stop' => $distanceMeta['nearest_stop'] ?? null,
            'lo_trinh_tuyens' => $route->loTrinhTuyens->map(function ($loTrinh) {
                return [
                    'id' => (int) $loTrinh->id,
                    'chieu' => $loTrinh->chieu,
                    'mo_ta_lo_trinh' => $loTrinh->mo_ta_lo_trinh,
                    'chi_tiet_lo_trinhs' => $loTrinh->chiTietLoTrinhs
                        ->filter(fn ($detail) => $detail->tramXe && $detail->tramXe->vi_do !== null && $detail->tramXe->kinh_do !== null)
                        ->map(function ($detail) {
                            return [
                                'id' => (int) $detail->id,
                                'thu_tu_dung' => (int) $detail->thu_tu_dung,
                                'tram_xe' => [
                                    'id' => (int) $detail->tramXe->id,
                                    'ma_tram' => $detail->tramXe->ma_tram,
                                    'ten_tram' => $detail->tramXe->ten_tram,
                                    'dia_chi' => $detail->tramXe->dia_chi,
                                    'vi_do' => (float) $detail->tramXe->vi_do,
                                    'kinh_do' => (float) $detail->tramXe->kinh_do,
                                ],
                            ];
                        })
                        ->values(),
                ];
            })->values(),
        ];
    }
}
