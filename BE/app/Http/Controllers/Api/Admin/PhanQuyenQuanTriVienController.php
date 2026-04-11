<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\ApiRequestException;
use App\Http\Requests\Admin\PhanQuyenQuanTriVien\DeletePhanQuyenQuanTriVienRequest;
use App\Http\Requests\Admin\PhanQuyenQuanTriVien\StorePhanQuyenQuanTriVienRequest;
use App\Http\Requests\Admin\PhanQuyenQuanTriVien\UpdatePhanQuyenQuanTriVienRequest;
use App\Models\ChucNang;
use App\Models\PhanQuyenQuanTriVien;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PhanQuyenQuanTriVienController extends BaseAdminController
{
    public function index(Request $request)
    {
        return $this->handle(function () use ($request) {
            $this->ensureMaster($request);

            $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

            $data = PhanQuyenQuanTriVien::query()
                ->with(['chucNangs:id,ten_chuc_nang,route_name,nhom'])
                ->withCount(['quanTriViens', 'chucNangs'])
                ->orderBy('ten_quyen')
                ->paginate($perPage);

            $this->hydrateFunctionData($data->getCollection());

            return $this->dataResponse($data);
        });
    }

    public function functionOptions(Request $request)
    {
        return $this->handle(function () use ($request) {
            $this->ensureMaster($request);

            $functions = ChucNang::query()
                ->orderBy('nhom')
                ->orderBy('ten_chuc_nang')
                ->get(['id', 'ten_chuc_nang', 'route_name', 'nhom', 'http_method', 'uri']);

            $grouped = $functions
                ->groupBy(fn (ChucNang $chucNang) => $chucNang->nhom ?: 'Khac')
                ->map(fn (Collection $items, string $group) => [
                    'label' => $group,
                    'options' => $items->map(fn (ChucNang $item) => [
                        'id' => $item->id,
                        'label' => $item->ten_chuc_nang,
                        'route_name' => $item->route_name,
                        'http_method' => $item->http_method,
                        'uri' => $item->uri,
                    ])->values()->all(),
                ])
                ->values()
                ->all();

            return $this->dataResponse([
                'data' => $grouped,
            ], 'Lay danh sach chuc nang thanh cong.');
        });
    }

    public function store(StorePhanQuyenQuanTriVienRequest $request)
    {
        return $this->handle(function () use ($request) {
            $this->ensureMaster($request);

            $payload = $request->validated();
            $functionIds = $payload['chuc_nang_ids'] ?? [];
            unset($payload['chuc_nang_ids']);

            $permission = PhanQuyenQuanTriVien::query()->create($payload);
            $permission->chucNangs()->sync($functionIds);

            $permission->load(['chucNangs:id,ten_chuc_nang,route_name,nhom'])->loadCount(['quanTriViens', 'chucNangs']);
            $this->hydrateFunctionData(collect([$permission]));

            return $this->successResponse($permission, 'Tao nhom quyen thanh cong.');
        });
    }

    public function show(Request $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $this->ensureMaster($request);

            $permission = PhanQuyenQuanTriVien::query()
                ->with(['chucNangs:id,ten_chuc_nang,route_name,nhom'])
                ->withCount(['quanTriViens', 'chucNangs'])
                ->findOrFail($id);

            $this->hydrateFunctionData(collect([$permission]));

            return $this->dataResponse($permission);
        });
    }

    public function update(UpdatePhanQuyenQuanTriVienRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $this->ensureMaster($request);

            $permission = PhanQuyenQuanTriVien::query()->findOrFail($id);
            $payload = $request->validated();
            $functionIds = $payload['chuc_nang_ids'] ?? null;
            unset($payload['chuc_nang_ids']);

            $permission->update($payload);

            if (is_array($functionIds)) {
                $permission->chucNangs()->sync($functionIds);
            }

            $permission = $permission->fresh(['chucNangs:id,ten_chuc_nang,route_name,nhom'])->loadCount(['quanTriViens', 'chucNangs']);
            $this->hydrateFunctionData(collect([$permission]));

            return $this->successResponse($permission, 'Cap nhat nhom quyen thanh cong.');
        });
    }

    public function destroy(DeletePhanQuyenQuanTriVienRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $this->ensureMaster($request);

            $permission = PhanQuyenQuanTriVien::query()
                ->withCount('quanTriViens')
                ->findOrFail($id);

            if ((int) $permission->quan_tri_viens_count > 0) {
                throw new ApiRequestException('Khong the xoa nhom quyen dang duoc gan cho quan tri vien.');
            }

            $permission->delete();

            return $this->successResponse(null, 'Xoa nhom quyen thanh cong.');
        });
    }

    protected function hydrateFunctionData(Collection $collection): void
    {
        $collection->transform(function (PhanQuyenQuanTriVien $permission) {
            $permission->setAttribute(
                'chuc_nang_ids',
                $permission->chucNangs->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
            );
            $permission->setAttribute(
                'ten_chuc_nangs',
                $permission->chucNangs->pluck('ten_chuc_nang')->values()->all()
            );

            return $permission;
        });
    }

    protected function ensureMaster(Request $request): void
    {
        $currentAdmin = $request->user();

        if (! $currentAdmin || (int) $currentAdmin->is_master !== 1) {
            throw new ApiRequestException('Chi tai khoan master moi co quyen quan ly nhom quyen.');
        }
    }
}
