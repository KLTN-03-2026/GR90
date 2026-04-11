<?php

namespace App\Http\Controllers\Api\Client;

use App\Models\KhachHang;
use App\Models\TuyenXe;
use App\Models\TuyenYeuThich;
use Illuminate\Http\Request;

class ClientFavoriteController extends BaseClientController
{
    public function index(Request $request)
    {
        return $this->handle(function () use ($request) {
            /** @var KhachHang $khachHang */
            $khachHang = $request->user();

            $items = TuyenYeuThich::query()
                ->where('khach_hang_id', $khachHang->id)
                ->with([
                    'tuyenXe.loaiTuyen',
                    'tuyenXe.trangThaiTuyen',
                    'tuyenXe.donViVanHanh',
                    'tuyenXe.loTrinhTuyens' => function ($query) {
                        $query->withCount('chiTietLoTrinhs')->orderBy('id');
                    },
                ])
                ->latest()
                ->paginate($this->resolvePerPage($request, 10, 30));

            $items->setCollection(
                $items->getCollection()->map(function (TuyenYeuThich $favorite) use ($khachHang) {
                    return [
                        'favorite_id' => $favorite->id,
                        'created_at' => optional($favorite->created_at)->format('d/m/Y H:i'),
                        'route' => $this->routeSummary($favorite->tuyenXe, $khachHang),
                    ];
                })
            );

            return $this->dataResponse($items, 'Lay danh sach tuyen yeu thich thanh cong.');
        });
    }

    public function store(Request $request, string $routeId)
    {
        return $this->handle(function () use ($request, $routeId) {
            /** @var KhachHang $khachHang */
            $khachHang = $request->user();
            $tuyenXe = TuyenXe::query()->findOrFail($routeId);

            $favorite = TuyenYeuThich::query()->firstOrCreate([
                'khach_hang_id' => $khachHang->id,
                'tuyen_xe_id' => $tuyenXe->id,
            ]);

            return $this->successResponse([
                'favorite_id' => $favorite->id,
                'route_id' => $tuyenXe->id,
            ], 'Da luu tuyen vao danh sach yeu thich.');
        });
    }

    public function destroy(Request $request, string $routeId)
    {
        return $this->handle(function () use ($request, $routeId) {
            /** @var KhachHang $khachHang */
            $khachHang = $request->user();

            TuyenYeuThich::query()
                ->where('khach_hang_id', $khachHang->id)
                ->where('tuyen_xe_id', $routeId)
                ->delete();

            return $this->successResponse(null, 'Da xoa tuyen khoi danh sach yeu thich.');
        });
    }
}
