<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\KhachHang\DeleteKhachHangRequest;
use App\Http\Requests\Admin\KhachHang\StoreKhachHangRequest;
use App\Http\Requests\Admin\KhachHang\UpdateKhachHangRequest;
use App\Models\KhachHang;

class KhachHangController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = KhachHang::query()
                ->latest()
                ->paginate(20);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreKhachHangRequest $request)
    {
        return $this->handle(function () use ($request) {
            $khachHang = KhachHang::query()->create($request->validated());

            return $this->successResponse($khachHang, 'Tao khach hang thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $khachHang = KhachHang::query()->findOrFail($id);

            return $this->dataResponse($khachHang);
        });
    }

    public function update(UpdateKhachHangRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $khachHang = KhachHang::query()->findOrFail($id);
            $khachHang->update($request->validated());

            return $this->successResponse($khachHang->fresh(), 'Cap nhat khach hang thanh cong.');
        });
    }

    public function destroy(DeleteKhachHangRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $khachHang = KhachHang::query()->findOrFail($id);
            $khachHang->delete();

            return $this->successResponse(null, 'Xoa khach hang thanh cong.');
        });
    }
}
