<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\ApiRequestException;
use App\Http\Requests\Admin\TrangThaiTuyen\DeleteTrangThaiTuyenRequest;
use App\Http\Requests\Admin\TrangThaiTuyen\StoreTrangThaiTuyenRequest;
use App\Http\Requests\Admin\TrangThaiTuyen\UpdateTrangThaiTuyenRequest;
use App\Models\TrangThaiTuyen;

class TrangThaiTuyenController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = TrangThaiTuyen::query()->withCount('tuyenXes')->latest()->paginate(20);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreTrangThaiTuyenRequest $request)
    {
        return $this->handle(function () use ($request) {
            $trangThai = TrangThaiTuyen::query()->create($request->validated());

            return $this->successResponse($trangThai, 'Tao trang thai tuyen thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $trangThai = TrangThaiTuyen::query()->with('tuyenXes')->findOrFail($id);

            return $this->dataResponse($trangThai);
        });
    }

    public function update(UpdateTrangThaiTuyenRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $trangThai = TrangThaiTuyen::query()->findOrFail($id);
            $trangThai->update($request->validated());

            return $this->successResponse($trangThai->fresh(), 'Cap nhat trang thai tuyen thanh cong.');
        });
    }

    public function destroy(DeleteTrangThaiTuyenRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $trangThai = TrangThaiTuyen::query()->withCount('tuyenXes')->findOrFail($id);
            if ($trangThai->tuyen_xes_count > 0) {
                throw new ApiRequestException('Khong the xoa trang thai tuyen dang duoc su dung.');
            }

            $trangThai->delete();

            return $this->successResponse(null, 'Xoa trang thai tuyen thanh cong.');
        });
    }
}
