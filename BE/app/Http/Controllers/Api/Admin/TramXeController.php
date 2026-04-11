<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\TramXe\DeleteTramXeRequest;
use App\Http\Requests\Admin\TramXe\StoreTramXeRequest;
use App\Http\Requests\Admin\TramXe\UpdateTramXeRequest;
use App\Models\TramXe;

class TramXeController extends BaseAdminController
{
    public function index()
    {
        return $this->handle(function () {
            $data = TramXe::query()->withCount('chiTietLoTrinhs')->latest()->paginate(20);

            return $this->dataResponse($data);
        });
    }

    public function store(StoreTramXeRequest $request)
    {
        return $this->handle(function () use ($request) {
            $tramXe = TramXe::query()->create($request->validated());

            return $this->successResponse($tramXe, 'Tao tram xe thanh cong.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $tramXe = TramXe::query()->with('chiTietLoTrinhs')->findOrFail($id);

            return $this->dataResponse($tramXe);
        });
    }

    public function update(UpdateTramXeRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $tramXe = TramXe::query()->findOrFail($id);
            $tramXe->update($request->validated());

            return $this->successResponse($tramXe->fresh(), 'Cap nhat tram xe thanh cong.');
        });
    }

    public function destroy(DeleteTramXeRequest $request, string $id)
    {
        return $this->handle(function () use ($id) {
            $tramXe = TramXe::query()->findOrFail($id);
            $tramXe->delete();

            return $this->successResponse(null, 'Xoa tram xe thanh cong.');
        });
    }
}
