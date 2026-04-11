<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\XeBuyt\DeleteXeBuytRequest;
use App\Http\Requests\Admin\XeBuyt\StoreXeBuytRequest;
use App\Http\Requests\Admin\XeBuyt\UpdateXeBuytRequest;
use App\Models\XeBuyt;

class XeBuytController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = XeBuyt::query()
                ->with('tuyenXe')
                ->latest()
                ->paginate(20);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreXeBuytRequest $request)
    {
        return $this->handle(function () use ($request) {
            $xeBuyt = XeBuyt::query()->create($request->validated());

            return $this->successResponse($xeBuyt->load('tuyenXe'), 'Tao xe buyt thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $xeBuyt = XeBuyt::query()->with('tuyenXe')->findOrFail($id);

            return $this->dataResponse($xeBuyt);
        });
    }

    public function update(UpdateXeBuytRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $xeBuyt = XeBuyt::query()->findOrFail($id);
            $xeBuyt->update($request->validated());

            return $this->successResponse($xeBuyt->fresh('tuyenXe'), 'Cap nhat xe buyt thanh cong.');
        });
    }

    public function destroy(DeleteXeBuytRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $xeBuyt = XeBuyt::query()->findOrFail($id);
            $xeBuyt->delete();

            return $this->successResponse(null, 'Xoa xe buyt thanh cong.');
        });
    }
}
