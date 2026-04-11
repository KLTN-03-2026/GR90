<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\ChiTietLoTrinh\DeleteChiTietLoTrinhRequest;
use App\Http\Requests\Admin\ChiTietLoTrinh\StoreChiTietLoTrinhRequest;
use App\Http\Requests\Admin\ChiTietLoTrinh\UpdateChiTietLoTrinhRequest;
use App\Models\ChiTietLoTrinh;

class ChiTietLoTrinhController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = ChiTietLoTrinh::query()
                ->with(['loTrinhTuyen.tuyenXe', 'tramXe'])
                ->orderBy('lo_trinh_tuyen_id')
                ->orderBy('thu_tu_dung')
                ->paginate(30);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreChiTietLoTrinhRequest $request)
    {
        return $this->handle(function () use ($request) {
            $chiTiet = ChiTietLoTrinh::query()->create($request->validated());

            return $this->successResponse($chiTiet->load(['loTrinhTuyen.tuyenXe', 'tramXe']), 'Tao chi tiet lo trinh thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $chiTiet = ChiTietLoTrinh::query()->with(['loTrinhTuyen.tuyenXe', 'tramXe'])->findOrFail($id);

            return $this->dataResponse($chiTiet);
        });
    }

    public function update(UpdateChiTietLoTrinhRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $chiTiet = ChiTietLoTrinh::query()->findOrFail($id);
            $chiTiet->update($request->validated());

            return $this->successResponse($chiTiet->fresh()->load(['loTrinhTuyen.tuyenXe', 'tramXe']), 'Cap nhat chi tiet lo trinh thanh cong.');
        });
    }

    public function destroy(DeleteChiTietLoTrinhRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $chiTiet = ChiTietLoTrinh::query()->findOrFail($id);
            $chiTiet->delete();

            return $this->successResponse(null, 'Xoa chi tiet lo trinh thanh cong.');
        });
    }
}
