<?php

namespace App\Http\Controllers\Api\Client;

use App\Models\KhachHang;
use App\Models\LuotXemTuyen;
use Illuminate\Http\Request;

class ClientViewedRouteController extends BaseClientController
{
    public function index(Request $request)
    {
        return $this->handle(function () use ($request) {
            /** @var KhachHang $khachHang */
            $khachHang = $request->user();

            $items = LuotXemTuyen::query()
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
                $items->getCollection()->map(function (LuotXemTuyen $luotXem) use ($khachHang) {
                    return [
                        'id' => $luotXem->id,
                        'created_at' => optional($luotXem->created_at)->format('d/m/Y H:i'),
                        'route' => $luotXem->tuyenXe ? $this->routeSummary($luotXem->tuyenXe, $khachHang) : null,
                    ];
                })->filter()->values()
            );

            return $this->dataResponse($items, 'Lay danh sach tuyen da xem thanh cong.');
        });
    }
}
