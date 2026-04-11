<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Requests\Client\History\StoreSearchHistoryRequest;
use App\Models\KhachHang;
use App\Models\LichSuTraCuu;
use Illuminate\Http\Request;

class ClientSearchHistoryController extends BaseClientController
{
    public function index(Request $request)
    {
        return $this->handle(function () use ($request) {
            /** @var KhachHang $khachHang */
            $khachHang = $request->user();

            $items = LichSuTraCuu::query()
                ->where('khach_hang_id', $khachHang->id)
                ->latest('thoi_gian_tra_cuu')
                ->paginate($this->resolvePerPage($request, 10, 30));

            $items->setCollection(
                $items->getCollection()->map(function (LichSuTraCuu $lichSu) {
                    return [
                        'id' => $lichSu->id,
                        'diem_di' => $lichSu->diem_di,
                        'diem_den' => $lichSu->diem_den,
                        'tu_khoa_tim_kiem' => $lichSu->tu_khoa_tim_kiem,
                        'ket_qua_goi_y_json' => $lichSu->ket_qua_goi_y_json,
                        'thoi_gian_tra_cuu' => optional($lichSu->thoi_gian_tra_cuu)->format('d/m/Y H:i'),
                    ];
                })
            );

            return $this->dataResponse($items, 'Lay lich su tra cuu thanh cong.');
        });
    }

    public function store(StoreSearchHistoryRequest $request)
    {
        return $this->handle(function () use ($request) {
            /** @var KhachHang $khachHang */
            $khachHang = $request->user();
            $payload = $request->validated();

            $lichSu = LichSuTraCuu::query()->create([
                'khach_hang_id' => $khachHang->id,
                'diem_di' => $payload['diem_di'] ?? null,
                'diem_den' => $payload['diem_den'] ?? null,
                'tu_khoa_tim_kiem' => $payload['tu_khoa_tim_kiem'] ?? null,
                'ket_qua_goi_y_json' => $payload['ket_qua_goi_y_json'] ?? null,
                'thoi_gian_tra_cuu' => now(),
            ]);

            return $this->successResponse([
                'id' => $lichSu->id,
            ], 'Da luu lich su tra cuu.');
        });
    }

    public function destroy(Request $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            /** @var KhachHang $khachHang */
            $khachHang = $request->user();

            LichSuTraCuu::query()
                ->where('khach_hang_id', $khachHang->id)
                ->where('id', $id)
                ->delete();

            return $this->successResponse(null, 'Da xoa muc lich su tra cuu.');
        });
    }

    public function clear(Request $request)
    {
        return $this->handle(function () use ($request) {
            /** @var KhachHang $khachHang */
            $khachHang = $request->user();

            LichSuTraCuu::query()
                ->where('khach_hang_id', $khachHang->id)
                ->delete();

            return $this->successResponse(null, 'Da xoa toan bo lich su tra cuu.');
        });
    }
}
