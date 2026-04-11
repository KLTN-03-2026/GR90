<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\ApiRequestException;
use App\Http\Requests\Admin\DonViVanHanh\DeleteDonViVanHanhRequest;
use App\Http\Requests\Admin\DonViVanHanh\StoreDonViVanHanhRequest;
use App\Http\Requests\Admin\DonViVanHanh\UpdateDonViVanHanhRequest;
use App\Models\DonViVanHanh;

class DonViVanHanhController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = DonViVanHanh::query()->withCount('tuyenXes')->latest()->paginate(20);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreDonViVanHanhRequest $request)
    {
        return $this->handle(function () use ($request) {
            $donVi = DonViVanHanh::query()->create($request->validated());

            return $this->successResponse($donVi, 'Tao don vi van hanh thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $donVi = DonViVanHanh::query()->with('tuyenXes')->findOrFail($id);

            return $this->dataResponse($donVi);
        });
    }

    public function update(UpdateDonViVanHanhRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $donVi = DonViVanHanh::query()->findOrFail($id);
            $donVi->update($request->validated());

            return $this->successResponse($donVi->fresh(), 'Cap nhat don vi van hanh thanh cong.');
        });
    }

    public function destroy(DeleteDonViVanHanhRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $donVi = DonViVanHanh::query()->withCount('tuyenXes')->findOrFail($id);
            if ($donVi->tuyen_xes_count > 0) {
                throw new ApiRequestException('Khong the xoa don vi van hanh dang duoc su dung.');
            }

            $donVi->delete();

            return $this->successResponse(null, 'Xoa don vi van hanh thanh cong.');
        });
    }
}
