<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\LoTrinhTuyen\DeleteLoTrinhTuyenRequest;
use App\Http\Requests\Admin\LoTrinhTuyen\StoreLoTrinhTuyenRequest;
use App\Http\Requests\Admin\LoTrinhTuyen\UpdateLoTrinhTuyenRequest;
use App\Models\LoTrinhTuyen;

class LoTrinhTuyenController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = LoTrinhTuyen::query()->with('tuyenXe')->latest()->paginate(20);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreLoTrinhTuyenRequest $request)
    {
        return $this->handle(function () use ($request) {
            $loTrinh = LoTrinhTuyen::query()->create($request->validated());

            return $this->successResponse($loTrinh->load('tuyenXe'), 'Tao lo trinh tuyen thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $loTrinh = LoTrinhTuyen::query()->with(['tuyenXe', 'chiTietLoTrinhs.tramXe'])->findOrFail($id);

            return $this->dataResponse($loTrinh);
        });
    }

    public function update(UpdateLoTrinhTuyenRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $loTrinh = LoTrinhTuyen::query()->findOrFail($id);
            $loTrinh->update($request->validated());

            return $this->successResponse($loTrinh->fresh()->load('tuyenXe'), 'Cap nhat lo trinh tuyen thanh cong.');
        });
    }

    public function destroy(DeleteLoTrinhTuyenRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $loTrinh = LoTrinhTuyen::query()->findOrFail($id);
            $loTrinh->delete();

            return $this->successResponse(null, 'Xoa lo trinh tuyen thanh cong.');
        });
    }
}
