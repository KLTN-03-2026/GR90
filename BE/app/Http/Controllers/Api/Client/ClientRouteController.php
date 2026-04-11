<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Requests\Client\Route\RecommendRouteRequest;
use App\Models\KhachHang;
use App\Models\LoTrinhTuyen;
use App\Models\LuotXemTuyen;
use App\Models\TramXe;
use App\Models\TuyenXe;
use App\Services\OsrmService;
use App\Services\RouteGraph\RouteGraphService;
use Database\Seeders\Concerns\InteractsWithBusMapApi;
use Illuminate\Http\Request;
use Throwable;

class ClientRouteController extends BaseClientController
{
    use InteractsWithBusMapApi;

    private array $fallbackStopCountCache = [];

    public function index(Request $request)
    {
        return $this->handle(function () use ($request) {
            $keyword = trim((string) $request->string('q'));
            $khachHang = $request->user();

            $query = TuyenXe::query()
                ->with([
                    'loaiTuyen',
                    'trangThaiTuyen',
                    'donViVanHanh',
                    'loTrinhTuyens' => function ($query) {
                        $query->withCount('chiTietLoTrinhs')->orderBy('id');
                    },
                ])
                ->withCount('luotXemTuyens')
                ->when(
                    $khachHang instanceof KhachHang,
                    fn ($query) => $query->withExists([
                        'tuyenYeuThichs as is_favorite' => fn ($favoriteQuery) => $favoriteQuery->where('khach_hang_id', $khachHang->id),
                    ])
                )
                ->when($request->filled('loai_tuyen_id'), fn ($query) => $query->where('loai_tuyen_id', $request->integer('loai_tuyen_id')))
                ->when($request->filled('trang_thai_tuyen_id'), fn ($query) => $query->where('trang_thai_tuyen_id', $request->integer('trang_thai_tuyen_id')))
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery->where('ma_tuyen', 'like', "%{$keyword}%")
                            ->orWhere('ten_tuyen', 'like', "%{$keyword}%")
                            ->orWhere('ten_tuyen_cu', 'like', "%{$keyword}%")
                            ->orWhere('diem_dau', 'like', "%{$keyword}%")
                            ->orWhere('diem_cuoi', 'like', "%{$keyword}%")
                            ->orWhereHas('loaiTuyen', fn ($loaiQuery) => $loaiQuery->where('ten_loai_tuyen', 'like', "%{$keyword}%"))
                            ->orWhereHas('tuKhoaTuyenXes', fn ($tuKhoaQuery) => $tuKhoaQuery->where('tu_khoa', 'like', "%{$keyword}%"));
                    });
                })
                ->orderBy('ma_tuyen');

            $data = $query->paginate($this->resolvePerPage($request, 10, 30));
            $items = collect($data->items())->map(function (TuyenXe $tuyenXe) use ($khachHang) {
                $summary = $this->routeSummary($tuyenXe, $khachHang instanceof KhachHang ? $khachHang : null);

                return $this->hydrateMissingRouteSummary($tuyenXe, $summary);
            });
            $data->setCollection($items);

            return $this->dataResponse($data, 'Lay danh sach tuyen xe thanh cong.');
        });
    }

    public function show(Request $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $khachHang = $request->user();
            $tuyenXe = TuyenXe::query()
                ->with([
                    'loaiTuyen',
                    'trangThaiTuyen',
                    'donViVanHanh',
                    'tuKhoaTuyenXes',
                    'giaVeTuyens',
                    'loTrinhTuyens' => function ($query) {
                        $query->withCount('chiTietLoTrinhs')
                            ->with([
                                'chiTietLoTrinhs' => function ($detailQuery) {
                                    $detailQuery->with('tramXe')->orderBy('thu_tu_dung');
                                },
                            ])
                            ->orderBy('id');
                    },
                ])
                ->withCount('luotXemTuyens')
                ->when(
                    $khachHang instanceof KhachHang,
                    fn ($query) => $query->withExists([
                        'tuyenYeuThichs as is_favorite' => fn ($favoriteQuery) => $favoriteQuery->where('khach_hang_id', $khachHang->id),
                    ])
                )
                ->findOrFail($id);

            $detail = $this->hydrateMissingRouteStops(
                $tuyenXe,
                $this->routeDetail($tuyenXe, $khachHang instanceof KhachHang ? $khachHang : null)
            );

            return $this->dataResponse(
                $detail,
                'Lay chi tiet tuyen xe thanh cong.'
            );
        });
    }

    public function geometry(string $id)
    {
        return $this->handle(function () use ($id) {
            $tuyenXe = TuyenXe::query()
                ->with([
                    'loTrinhTuyens' => function ($query) {
                        $query->with([
                            'chiTietLoTrinhs' => function ($detailQuery) {
                                $detailQuery->with('tramXe')->orderBy('thu_tu_dung');
                            },
                        ])->orderBy('id');
                    },
                ])
                ->findOrFail($id);

            $osrm = new OsrmService;
            $directions = [];

            foreach ($tuyenXe->loTrinhTuyens as $loTrinh) {
                $coordinates = collect($loTrinh->chiTietLoTrinhs ?? [])
                    ->map(fn ($chiTiet) => $chiTiet->tramXe)
                    ->filter(fn ($tramXe) => $tramXe !== null)
                    ->map(fn ($tramXe) => [(float) $tramXe->kinh_do, (float) $tramXe->vi_do])
                    ->filter(fn ($coord) => is_finite($coord[0]) && is_finite($coord[1]))
                    ->values()
                    ->all();

                $geometry = $osrm->routeCoordinates($coordinates);

                $directions[] = [
                    'chieu' => $loTrinh->chieu,
                    'geometry' => $geometry,
                ];
            }

            return $this->dataResponse([
                'tuyen_xe_id' => $tuyenXe->id,
                'directions' => $directions,
            ], 'Lay geometry tuyen xe thanh cong.');
        });
    }

    public function recommend(RecommendRouteRequest $request)
    {
        return $this->handle(function () use ($request) {
            $payload = $request->validated();
            $diemDi  = trim((string) ($payload['diem_di'] ?? ''));
            $diemDen = trim((string) ($payload['diem_den'] ?? ''));
            $tuKhoa  = trim((string) ($payload['tu_khoa'] ?? ''));
            $limit   = (int) ($payload['limit'] ?? 8);

            $routeGraph = new RouteGraphService;
            $result = $routeGraph->findRoutes($diemDi, $diemDen, $tuKhoa, $limit);

            $hasItems = ! empty($result['items']);

            if ($hasItems) {
                return $this->dataResponse($result, 'Goi y tuyen xe thanh cong.');
            }

            $fallbackData = $this->recommendFallback($diemDi, $diemDen, $tuKhoa, $limit);
            $result['items'] = $fallbackData;
            $message = empty($fallbackData)
                ? 'Khong tim duoc tuyen xe phu hop.'
                : 'Goi y tuyen xe thanh cong.';

            return $this->dataResponse($result, $message);
        });
    }

    /**
     * Fallback: Dùng logic cũ (foreach tuyến + string matching).
     * Chạy khi graph routing không tìm được kết quả.
     */
    private function recommendFallback(string $diemDi, string $diemDen, string $tuKhoa, int $limit): array
    {
        $routes = TuyenXe::query()
            ->with([
                'loaiTuyen',
                'trangThaiTuyen',
                'donViVanHanh',
                'loTrinhTuyens' => function ($query) {
                    $query->with([
                        'chiTietLoTrinhs' => function ($detailQuery) {
                            $detailQuery->with('tramXe')->orderBy('thu_tu_dung');
                        },
                    ])->withCount('chiTietLoTrinhs');
                },
                'tuKhoaTuyenXes',
            ])
            ->get();

        $suggestions = collect();

        foreach ($routes as $route) {
            $matchedByKeyword = $tuKhoa !== '' && $this->routeMatchesKeyword($route, $tuKhoa);

            foreach ($route->loTrinhTuyens as $loTrinh) {
                $matchedStart = $this->findMatchedStops($loTrinh, $diemDi);
                $matchedEnd   = $this->findMatchedStops($loTrinh, $diemDen);

                $isValid = false;
                if ($diemDi !== '' && $diemDen !== '') {
                    $isValid = $matchedStart['count'] > 0
                        && $matchedEnd['count'] > 0;
                } elseif ($diemDi !== '') {
                    $isValid = $matchedStart['count'] > 0;
                } elseif ($diemDen !== '') {
                    $isValid = $matchedEnd['count'] > 0;
                } elseif ($matchedByKeyword) {
                    $isValid = true;
                }

                if (! $isValid && ! $matchedByKeyword) {
                    continue;
                }

                $score = 0;
                if ($matchedByKeyword) {
                    $score += 2;
                }
                if ($matchedStart['count'] > 0) {
                    $score += 3;
                }
                if ($matchedEnd['count'] > 0) {
                    $score += 3;
                }
                if ($matchedStart['count'] > 0 && $matchedEnd['count'] > 0) {
                    $score += 2;
                }

                $suggestions->push([
                    'score'          => $score,
                    'route'          => $route,
                    'direction'      => $loTrinh->chieu,
                    'matched_start'  => $matchedStart['stops'],
                    'matched_end'    => $matchedEnd['stops'],
                    'summary'        => $this->routeSummary($route),
                    'loTrinh'        => $loTrinh,
                ]);
            }
        }

            return $suggestions
            ->sortByDesc('score')
            ->unique(fn ($item) => $item['route']->id.':'.$item['direction'])
            ->take($limit)
            ->values()
            ->map(function (array $item) {
                $loTrinh = $item['loTrinh'];
                $startOrder = $item['matched_start'][0]['thu_tu_dung'] ?? null;
                $endOrder = $item['matched_end'][0]['thu_tu_dung'] ?? null;

                $legStops = [];
                $totalDurationSec = 0;
                $totalDistanceM = 0;

                if ($loTrinh && $startOrder !== null && $endOrder !== null) {
                    $legStops = collect($loTrinh->chiTietLoTrinhs ?? [])
                        ->filter(fn ($chiTiet) =>
                            $chiTiet->thu_tu_dung >= min($startOrder, $endOrder)
                            && $chiTiet->thu_tu_dung <= max($startOrder, $endOrder)
                        )
                        ->values()
                        ->all();

                    $totalDurationSec = count($legStops) * 120; // ~2 phút / trạm
                    $totalDistanceM = collect($legStops)
                        ->sum(fn ($chiTiet) => (float) ($chiTiet->khoang_cach_tu_diem_truoc_met ?? 0));
                }

                return [
                    'type'    => 'direct',
                    'score'   => $item['score'],
                    'direction' => $item['direction'],
                    'route'   => $item['summary'],
                    'matched_start' => collect($item['matched_start'])->map(fn ($stop) => [
                        'id'         => $stop['id'],
                        'ten_tram'   => $stop['ten_tram'],
                        'dia_chi'    => $stop['dia_chi'],
                        'thu_tu_dung' => $stop['thu_tu_dung'],
                    ])->values()->all(),
                    'matched_end' => collect($item['matched_end'])->map(fn ($stop) => [
                        'id'         => $stop['id'],
                        'ten_tram'   => $stop['ten_tram'],
                        'dia_chi'    => $stop['dia_chi'],
                        'thu_tu_dung' => $stop['thu_tu_dung'],
                    ])->values()->all(),
                    'total_transfers'   => 0,
                    'total_duration_min' => (int) ceil($totalDurationSec / 60),
                    'total_distance_km'  => round($totalDistanceM / 1000, 1),
                ];
            })
            ->all();
    }

    public function recordView(Request $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $tuyenXe = TuyenXe::query()->findOrFail($id);
            $khachHang = $request->user();

            LuotXemTuyen::query()->create([
                'khach_hang_id' => $khachHang instanceof KhachHang ? $khachHang->id : null,
                'tuyen_xe_id' => $tuyenXe->id,
                'dia_chi_ip' => $request->ip(),
                'thiet_bi' => substr((string) $request->userAgent(), 0, 100),
            ]);

            return $this->successResponse([
                'route_id' => $tuyenXe->id,
            ], 'Da ghi nhan luot xem tuyen.');
        });
    }

    private function routeMatchesKeyword(TuyenXe $route, string $keyword): bool
    {
        $keyword = mb_strtolower($keyword);
        $haystack = collect([
            $route->ma_tuyen,
            $route->ten_tuyen,
            $route->ten_tuyen_cu,
            $route->diem_dau,
            $route->diem_cuoi,
            $route->loaiTuyen?->ten_loai_tuyen,
        ])->merge(collect($route->tuKhoaTuyenXes ?? [])->pluck('tu_khoa'));

        return $haystack
            ->filter()
            ->contains(fn ($value) => str_contains(mb_strtolower((string) $value), $keyword));
    }

    private function findMatchedStops(LoTrinhTuyen $loTrinhTuyen, string $keyword): array
    {
        if ($keyword === '') {
            return [
                'count' => 0,
                'first_order' => null,
                'last_order' => null,
                'stops' => [],
            ];
        }

        $normalizedKeyword = mb_strtolower($keyword);
        $matchedStops = collect($loTrinhTuyen->chiTietLoTrinhs ?? [])
            ->filter(function ($chiTiet) use ($normalizedKeyword) {
                $tramXe = $chiTiet->tramXe;
                $haystack = [
                    $tramXe?->ten_tram,
                    $tramXe?->dia_chi,
                    $chiTiet->ten_diem_di_qua,
                ];

                return collect($haystack)
                    ->filter()
                    ->contains(fn ($value) => str_contains(mb_strtolower((string) $value), $normalizedKeyword));
            })
            ->map(function ($chiTiet) {
                return [
                    'id' => $chiTiet->tramXe?->id,
                    'ten_tram' => $chiTiet->tramXe?->ten_tram,
                    'dia_chi' => $chiTiet->tramXe?->dia_chi,
                    'thu_tu_dung' => $chiTiet->thu_tu_dung,
                ];
            })
            ->values();

        return [
            'count' => $matchedStops->count(),
            'first_order' => $matchedStops->min('thu_tu_dung'),
            'last_order' => $matchedStops->max('thu_tu_dung'),
            'stops' => $matchedStops->all(),
        ];
    }

    private function hydrateMissingRouteStops(TuyenXe $tuyenXe, array $payload): array
    {
        $directions = collect($payload['lo_trinh_tuyens'] ?? []);
        $needsFallback = $directions->isEmpty()
            || $directions->contains(fn ($direction) => empty($direction['chi_tiet_lo_trinhs']));

        if (! $needsFallback) {
            return $payload;
        }

        $routeId = $this->extractBusMapRouteId($tuyenXe);
        if (! $routeId) {
            return $payload;
        }

        try {
            $detail = $this->busMapRouteDetail($routeId, 'dn');
        } catch (Throwable) {
            return $payload;
        }

        $stations = is_array($detail['stations'] ?? null) ? $detail['stations'] : [];
        if ($stations === []) {
            return $payload;
        }

        $fallbackDirections = $this->buildFallbackDirections($stations);
        if ($fallbackDirections === []) {
            return $payload;
        }

        $mergedDirections = collect(['di', 've'])
            ->map(function (string $chieu) use ($directions, $fallbackDirections) {
                $existing = $directions->first(fn ($direction) => ($direction['chieu'] ?? 'di') === $chieu);
                $fallback = $fallbackDirections[$chieu] ?? null;

                if ($existing && ! empty($existing['chi_tiet_lo_trinhs'])) {
                    return $existing;
                }

                if ($existing && $fallback) {
                    $existing['mo_ta_lo_trinh'] = $existing['mo_ta_lo_trinh'] ?: $fallback['mo_ta_lo_trinh'];
                    $existing['chi_tiet_lo_trinhs'] = $fallback['chi_tiet_lo_trinhs'];

                    return $existing;
                }

                return $fallback;
            })
            ->filter()
            ->values();

        if ($mergedDirections->isEmpty()) {
            return $payload;
        }

        $payload['lo_trinh_tuyens'] = $mergedDirections->all();
        $payload['tong_diem_dung'] = $mergedDirections->sum(fn ($direction) => count($direction['chi_tiet_lo_trinhs'] ?? []));
        $payload['huong_di'] = $mergedDirections
            ->map(function ($direction) {
                return [
                    'id' => $direction['id'] ?? null,
                    'chieu' => $direction['chieu'] ?? 'di',
                    'tong_diem_dung' => count($direction['chi_tiet_lo_trinhs'] ?? []),
                ];
            })
            ->all();

        return $payload;
    }

    private function hydrateMissingRouteSummary(TuyenXe $tuyenXe, array $summary): array
    {
        if ((int) ($summary['tong_diem_dung'] ?? 0) > 0) {
            return $summary;
        }

        $directionCounts = $this->resolveFallbackDirectionCounts($tuyenXe);
        if ($directionCounts === []) {
            return $summary;
        }

        $summary['tong_diem_dung'] = array_sum($directionCounts);
        $summary['huong_di'] = collect($summary['huong_di'] ?? [])
            ->map(function ($direction) use ($directionCounts) {
                $chieu = $direction['chieu'] ?? 'di';
                $direction['tong_diem_dung'] = (int) ($directionCounts[$chieu] ?? ($direction['tong_diem_dung'] ?? 0));

                return $direction;
            })
            ->all();

        if ($summary['huong_di'] === []) {
            $summary['huong_di'] = collect(['di', 've'])
                ->filter(fn (string $chieu) => isset($directionCounts[$chieu]))
                ->map(fn (string $chieu) => [
                    'id' => null,
                    'chieu' => $chieu,
                    'tong_diem_dung' => (int) $directionCounts[$chieu],
                ])
                ->values()
                ->all();
        }

        return $summary;
    }

    private function extractBusMapRouteId(TuyenXe $tuyenXe): ?int
    {
        if (preg_match('/routeId=(\d+)/', (string) $tuyenXe->ghi_chu, $matches) !== 1) {
            return null;
        }

        $routeId = (int) ($matches[1] ?? 0);

        return $routeId > 0 ? $routeId : null;
    }

    private function resolveFallbackDirectionCounts(TuyenXe $tuyenXe): array
    {
        $routeId = $this->extractBusMapRouteId($tuyenXe);
        if (! $routeId) {
            return [];
        }

        if (array_key_exists($routeId, $this->fallbackStopCountCache)) {
            return $this->fallbackStopCountCache[$routeId];
        }

        try {
            $detail = $this->busMapRouteDetail($routeId, 'dn');
        } catch (Throwable) {
            return $this->fallbackStopCountCache[$routeId] = [];
        }

        $stations = is_array($detail['stations'] ?? null) ? $detail['stations'] : [];
        if ($stations === []) {
            return $this->fallbackStopCountCache[$routeId] = [];
        }

        $counts = [
            'di' => 0,
            've' => 0,
        ];

        foreach ($stations as $station) {
            $direction = ((int) ($station['stationDirection'] ?? 0) === 1) ? 've' : 'di';
            $counts[$direction]++;
        }

        return $this->fallbackStopCountCache[$routeId] = $counts;
    }

    private function buildFallbackDirections(array $stations): array
    {
        $grouped = [
            'di' => [],
            've' => [],
        ];

        foreach ($stations as $station) {
            $direction = ((int) ($station['stationDirection'] ?? 0) === 1) ? 've' : 'di';
            $grouped[$direction][] = $station;
        }

        foreach (['di', 've'] as $direction) {
            usort($grouped[$direction], fn ($a, $b) => (int) ($a['stationOrder'] ?? 0) <=> (int) ($b['stationOrder'] ?? 0));
        }

        $maTrams = collect($stations)
            ->map(function ($station) {
                $stationId = (int) ($station['stationId'] ?? 0);

                return $stationId > 0 ? 'DN_TRAM_'.$stationId : null;
            })
            ->filter()
            ->unique()
            ->values();

        $tramByMa = TramXe::query()
            ->whereIn('ma_tram', $maTrams)
            ->get()
            ->keyBy('ma_tram');

        $result = [];

        foreach (['di', 've'] as $direction) {
            if ($grouped[$direction] === []) {
                continue;
            }

            $items = collect($grouped[$direction])
                ->values()
                ->map(function ($station, int $index) use ($direction, $tramByMa) {
                    $stationId = (int) ($station['stationId'] ?? 0);
                    $maTram = $stationId > 0 ? 'DN_TRAM_'.$stationId : null;
                    $tramXe = $maTram ? $tramByMa->get($maTram) : null;

                    return [
                        'id' => 'busmap-'.$direction.'-'.($stationId > 0 ? $stationId : $index + 1),
                        'thu_tu_dung' => (int) ($station['stationOrder'] ?? ($index + 1)),
                        'ten_diem_di_qua' => trim((string) ($station['stationName'] ?? '')),
                        'thoi_gian_dung_du_kien_giay' => null,
                        'khoang_cach_tu_diem_truoc_met' => null,
                        'tram_xe' => $tramXe
                            ? $this->stopSummary($tramXe)
                            : $this->fallbackStopSummary($station, $maTram),
                    ];
                })
                ->sortBy('thu_tu_dung')
                ->values()
                ->all();

            $firstName = trim((string) ($grouped[$direction][0]['stationName'] ?? ''));
            $lastName = trim((string) ($grouped[$direction][count($grouped[$direction]) - 1]['stationName'] ?? ''));

            $result[$direction] = [
                'id' => null,
                'chieu' => $direction,
                'mo_ta_lo_trinh' => $this->buildDirectionDescription($firstName, $lastName),
                'chi_tiet_lo_trinhs' => $items,
            ];
        }

        return $result;
    }

    private function fallbackStopSummary(array $station, ?string $maTram): array
    {
        return [
            'id' => null,
            'ma_cong_khai' => null,
            'ma_tram' => $maTram,
            'ten_tram' => trim((string) ($station['stationName'] ?? '')),
            'dia_chi' => trim((string) ($station['stationAddress'] ?? '')),
            'khu_vuc' => 'Đà Nẵng',
            'vi_do' => is_numeric($station['lat'] ?? null) ? (float) $station['lat'] : null,
            'kinh_do' => is_numeric($station['lng'] ?? null) ? (float) $station['lng'] : null,
        ];
    }

    private function buildDirectionDescription(string $firstName, string $lastName): string
    {
        $first = $firstName !== '' ? $firstName : 'Điểm đầu';
        $last = $lastName !== '' ? $lastName : 'Điểm cuối';

        return $first.' -> '.$last;
    }
}
