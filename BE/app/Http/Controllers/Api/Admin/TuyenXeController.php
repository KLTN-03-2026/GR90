<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\TuyenXe\DeleteTuyenXeRequest;
use App\Http\Requests\Admin\TuyenXe\StoreTuyenXeRequest;
use App\Http\Requests\Admin\TuyenXe\UpdateTuyenXeRequest;
use App\Models\TuyenXe;

class TuyenXeController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = TuyenXe::query()
                ->with(['loaiTuyen', 'trangThaiTuyen', 'donViVanHanh'])
                ->latest()
                ->paginate(20);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreTuyenXeRequest $request)
    {
        return $this->handle(function () use ($request) {
            $tuyenXe = TuyenXe::query()->create($request->validated());

            return $this->successResponse($tuyenXe->load(['loaiTuyen', 'trangThaiTuyen', 'donViVanHanh']), 'Tao tuyen xe thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $tuyenXe = TuyenXe::query()
                ->with([
                    'loaiTuyen',
                    'trangThaiTuyen',
                    'donViVanHanh',
                    'giaVeTuyens',
                    'loTrinhTuyens' => function ($query) {
                        $query->with([
                            'chiTietLoTrinhs' => function ($chiTietQuery) {
                                $chiTietQuery->with('tramXe')->orderBy('thu_tu_dung');
                            },
                        ])->orderBy('id');
                    },
                ])
                ->findOrFail($id);

            return $this->dataResponse($tuyenXe);
        });
    }

    public function update(UpdateTuyenXeRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $tuyenXe = TuyenXe::query()->findOrFail($id);
            $tuyenXe->update($request->validated());

            return $this->successResponse($tuyenXe->fresh()->load(['loaiTuyen', 'trangThaiTuyen', 'donViVanHanh']), 'Cap nhat tuyen xe thanh cong.');
        });
    }

    public function destroy(DeleteTuyenXeRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $tuyenXe = TuyenXe::query()->findOrFail($id);
            $tuyenXe->delete();

            return $this->successResponse(null, 'Xoa tuyen xe thanh cong.');
        });
    }
}
