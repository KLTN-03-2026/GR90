<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\ApiRequestException;
use App\Http\Requests\Admin\LoaiTuyen\DeleteLoaiTuyenRequest;
use App\Http\Requests\Admin\LoaiTuyen\StoreLoaiTuyenRequest;
use App\Http\Requests\Admin\LoaiTuyen\UpdateLoaiTuyenRequest;
use App\Models\LoaiTuyen;

class LoaiTuyenController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = LoaiTuyen::query()->withCount('tuyenXes')->latest()->paginate(20);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreLoaiTuyenRequest $request)
    {
        return $this->handle(function () use ($request) {
            $loaiTuyen = LoaiTuyen::query()->create($request->validated());

            return $this->successResponse($loaiTuyen, 'Tao loai tuyen thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $loaiTuyen = LoaiTuyen::query()->with('tuyenXes')->findOrFail($id);

            return $this->dataResponse($loaiTuyen);
        });
    }

    public function update(UpdateLoaiTuyenRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $loaiTuyen = LoaiTuyen::query()->findOrFail($id);
            $loaiTuyen->update($request->validated());

            return $this->successResponse($loaiTuyen->fresh(), 'Cap nhat loai tuyen thanh cong.');
        });
    }

    public function destroy(DeleteLoaiTuyenRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $loaiTuyen = LoaiTuyen::query()->withCount('tuyenXes')->findOrFail($id);
            if ($loaiTuyen->tuyen_xes_count > 0) {
                throw new ApiRequestException('Khong the xoa loai tuyen dang duoc su dung.');
            }

            $loaiTuyen->delete();

            return $this->successResponse(null, 'Xoa loai tuyen thanh cong.');
        });
    }
}
