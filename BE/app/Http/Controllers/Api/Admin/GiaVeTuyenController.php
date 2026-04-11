<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\GiaVeTuyen\DeleteGiaVeTuyenRequest;
use App\Http\Requests\Admin\GiaVeTuyen\StoreGiaVeTuyenRequest;
use App\Http\Requests\Admin\GiaVeTuyen\UpdateGiaVeTuyenRequest;
use App\Models\GiaVeTuyen;

class GiaVeTuyenController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = GiaVeTuyen::query()->with('tuyenXe.loaiTuyen')->latest()->paginate(20);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreGiaVeTuyenRequest $request)
    {
        return $this->handle(function () use ($request) {
            $giaVe = GiaVeTuyen::query()->create($request->validated());

            return $this->successResponse($giaVe->load('tuyenXe.loaiTuyen'), 'Tao gia ve tuyen thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $giaVe = GiaVeTuyen::query()->with('tuyenXe.loaiTuyen')->findOrFail($id);

            return $this->dataResponse($giaVe);
        });
    }

    public function update(UpdateGiaVeTuyenRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $giaVe = GiaVeTuyen::query()->findOrFail($id);
            $giaVe->update($request->validated());

            return $this->successResponse($giaVe->fresh()->load('tuyenXe.loaiTuyen'), 'Cap nhat gia ve tuyen thanh cong.');
        });
    }

    public function destroy(DeleteGiaVeTuyenRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $giaVe = GiaVeTuyen::query()->findOrFail($id);
            $giaVe->delete();

            return $this->successResponse(null, 'Xoa gia ve tuyen thanh cong.');
        });
    }
}
