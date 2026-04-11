<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Requests\Client\Route\NearbyStopRequest;
use App\Models\TramXe;
use App\Services\RouteGraph\RouteGraphService;
use Illuminate\Support\Facades\DB;

class ClientStopController extends BaseClientController
{
    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $tramXe = TramXe::query()
                ->with([
                    'chiTietLoTrinhs.loTrinhTuyen.tuyenXe.loaiTuyen',
                    'chiTietLoTrinhs.loTrinhTuyen.tuyenXe.trangThaiTuyen',
                ])
                ->findOrFail($id);

            return $this->dataResponse($this->stopDetail($tramXe), 'Lay chi tiet tram xe thanh cong.');
        });
    }

    /**
     * Trả về danh sách trạm có thể đến được từ một trạm (theo đồ thị, có chuyển tuyến).
     * Dùng để lọc dropdown điểm đến khi user đã chọn điểm đi.
     *
     * GET /api/client/stops/reachable-from/{id}
     */
    public function reachableFrom(string $id)
    {
        return $this->handle(function () use ($id) {
            $tramXe = TramXe::query()->findOrFail($id);
            $limit  = max(1, min((int) request()->integer('limit', 120), 250));

            $service = new RouteGraphService;
            $reachableIds = $service->getReachableStops((int) $id, 2);

            if (empty($reachableIds)) {
                return $this->dataResponse([
                    'from_stop' => [
                        'id'      => $tramXe->id,
                        'ten_tram' => $tramXe->ten_tram,
                        'dia_chi' => $tramXe->dia_chi,
                    ],
                    'items' => [],
                ], 'Khong co tram nao duoc tim thay.');
            }

            $stopIds = array_slice($reachableIds, 0, $limit);

            $items = TramXe::query()
                ->select('id', 'ten_tram', 'dia_chi', 'khu_vuc')
                ->whereIn('id', $stopIds)
                ->orderBy('ten_tram')
                ->get()
                ->map(fn ($tram) => [
                    'id'       => $tram->id,
                    'ten_tram' => $tram->ten_tram,
                    'dia_chi'  => $tram->dia_chi,
                    'khu_vuc'  => $tram->khu_vuc,
                ])
                ->values()
                ->all();

            return $this->dataResponse([
                'from_stop' => [
                    'id'       => $tramXe->id,
                    'ten_tram' => $tramXe->ten_tram,
                    'dia_chi'  => $tramXe->dia_chi,
                ],
                'items' => $items,
            ], 'Lay danh sach tram den duoc thanh cong.');
        });
    }

    public function nearby(NearbyStopRequest $request)
    {
        return $this->handle(function () use ($request) {
            $payload = $request->validated();
            $lat = (float) $payload['lat'];
            $lng = (float) $payload['lng'];
            $radius = (int) ($payload['radius'] ?? 200);
            $limit = (int) ($payload['limit'] ?? 10);

            $distanceSql = '(6371000 * acos(cos(radians(?)) * cos(radians(vi_do)) * cos(radians(kinh_do) - radians(?)) + sin(radians(?)) * sin(radians(vi_do))))';

            $items = TramXe::query()
                ->select('*')
                ->selectRaw("{$distanceSql} as distance_m", [$lat, $lng, $lat])
                ->whereNotNull('vi_do')
                ->whereNotNull('kinh_do')
                ->having('distance_m', '<=', $radius)
                ->orderBy('distance_m')
                ->limit($limit)
                ->get();

            $routeIdsByStop = DB::table('chi_tiet_lo_trinhs')
                ->join('lo_trinh_tuyens', 'lo_trinh_tuyens.id', '=', 'chi_tiet_lo_trinhs.lo_trinh_tuyen_id')
                ->whereIn('chi_tiet_lo_trinhs.tram_xe_id', $items->pluck('id'))
                ->select('chi_tiet_lo_trinhs.tram_xe_id', 'lo_trinh_tuyens.tuyen_xe_id')
                ->get()
                ->groupBy('tram_xe_id');

            $data = $items->map(function (TramXe $tramXe) use ($routeIdsByStop) {
                $routeIds = collect($routeIdsByStop->get($tramXe->id, []))
                    ->pluck('tuyen_xe_id')
                    ->unique()
                    ->values()
                    ->all();

                return array_merge($this->stopSummary($tramXe), [
                    'distance_m' => round((float) $tramXe->distance_m, 1),
                    'tuyen_xe_ids' => $routeIds,
                    'tong_tuyen_di_qua' => count($routeIds),
                ]);
            })->values()->all();

            return $this->dataResponse([
                'center' => [
                    'lat' => $lat,
                    'lng' => $lng,
                    'radius' => $radius,
                ],
                'items' => $data,
            ], 'Lay danh sach tram gan ban thanh cong.');
        });
    }

    public function suggestions()
    {
        return $this->handle(function () {
            $q     = trim((string) request()->string('q', ''));
            $limit = (int) (request()->integer('limit', 8));
            $limit = max(1, min($limit, 120));
            // Khi không gõ từ khóa: trả về một lô trạm có tuyến (để dropdown mở ra có dữ liệu)
            $effectiveLimit = $q === '' ? min($limit, 80) : $limit;

            // Chỉ trả về trạm có trong ít nhất 1 tuyến (join chi_tiet_lo_trinhs)
            $items = TramXe::query()
                ->select('tram_xes.id', 'tram_xes.ten_tram', 'tram_xes.dia_chi', 'tram_xes.khu_vuc')
                ->distinct()
                ->join('chi_tiet_lo_trinhs', 'chi_tiet_lo_trinhs.tram_xe_id', '=', 'tram_xes.id')
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($sub) use ($q) {
                        $sub->where('tram_xes.ten_tram', 'like', "%{$q}%")
                            ->orWhere('tram_xes.dia_chi', 'like', "%{$q}%")
                            ->orWhere('tram_xes.ma_tram', 'like', "%{$q}%")
                            ->orWhere('tram_xes.khu_vuc', 'like', "%{$q}%");
                    });
                })
                ->orderBy('tram_xes.ten_tram')
                ->limit($effectiveLimit)
                ->get()
                ->map(fn ($tramXe) => [
                    'id'      => $tramXe->id,
                    'ten_tram' => $tramXe->ten_tram,
                    'dia_chi' => $tramXe->dia_chi,
                    'khu_vuc' => $tramXe->khu_vuc,
                ])
                ->values()
                ->all();

            return $this->dataResponse(['items' => $items], 'Tra cuu tram thanh cong.');
        });
    }
}
