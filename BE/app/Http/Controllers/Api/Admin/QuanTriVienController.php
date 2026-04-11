<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\ApiRequestException;
use App\Http\Requests\Admin\QuanTriVien\DeleteQuanTriVienRequest;
use App\Http\Requests\Admin\QuanTriVien\StoreQuanTriVienRequest;
use App\Http\Requests\Admin\QuanTriVien\UpdateQuanTriVienRequest;
use App\Models\PhanQuyenQuanTriVien;
use App\Models\QuanTriVien;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class QuanTriVienController extends BaseAdminController
{
    public function index(Request $request)
    {
        return $this->handle(function () use ($request) {
            $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

            $data = QuanTriVien::query()
                ->with('quyenQuanTriViens:id,ten_quyen')
                ->latest()
                ->paginate($perPage);

            $this->hydratePermissionData($data->getCollection());

            return $this->dataResponse($data);
        });
    }

    public function permissionOptions(Request $request)
    {
        return $this->handle(function () use ($request) {
            $this->ensureCanManagePermissions($request, ['quyen_ids' => []]);

            $permissions = PhanQuyenQuanTriVien::query()
                ->withCount('chucNangs')
                ->orderBy('ten_quyen')
                ->get(['id', 'ten_quyen', 'mo_ta']);

            return $this->dataResponse([
                'data' => $permissions,
            ], 'Lấy danh sách quyền thành công.');
        });
    }

    public function store(StoreQuanTriVienRequest $request)
    {
        return $this->handle(function () use ($request) {
            $payload = $request->validated();
            $quyenIds = $payload['quyen_ids'] ?? [];
            unset($payload['quyen_ids']);

            $this->ensureCanAssignMaster($request, $payload);
            $this->ensureCanManagePermissions($request, ['quyen_ids' => $quyenIds]);

            $quanTriVien = QuanTriVien::query()->create($payload);
            $quanTriVien->quyenQuanTriViens()->sync($quyenIds);

            $quanTriVien->load('quyenQuanTriViens:id,ten_quyen');
            $this->hydratePermissionData(collect([$quanTriVien]));

            return $this->successResponse($quanTriVien, 'Tạo quản trị viên thành công.');
        });
    }

    public function show(string $id)
    {
        return $this->handle(function () use ($id) {
            $quanTriVien = QuanTriVien::query()
                ->with('quyenQuanTriViens:id,ten_quyen')
                ->findOrFail($id);

            $this->hydratePermissionData(collect([$quanTriVien]));

            return $this->dataResponse($quanTriVien);
        });
    }

    public function update(UpdateQuanTriVienRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $quanTriVien = QuanTriVien::query()
                ->with('quyenQuanTriViens:id,ten_quyen')
                ->findOrFail($id);

            $payload = $request->validated();
            $quyenIds = $payload['quyen_ids'] ?? null;
            unset($payload['quyen_ids']);

            $this->ensureCanManageMasterAccount($request, $quanTriVien);
            $this->ensureCanAssignMaster($request, $payload);
            $this->ensureCanManagePermissions($request, ['quyen_ids' => $quyenIds]);

            $quanTriVien->update($payload);

            if (is_array($quyenIds)) {
                $quanTriVien->quyenQuanTriViens()->sync($quyenIds);
            }

            $quanTriVien = $quanTriVien->fresh(['quyenQuanTriViens:id,ten_quyen']);
            $this->hydratePermissionData(collect([$quanTriVien]));

            return $this->successResponse($quanTriVien, 'Cập nhật quản trị viên thành công.');
        });
    }

    public function destroy(DeleteQuanTriVienRequest $request, string $id)
    {
        return $this->handle(function () use ($request, $id) {
            $quanTriVien = QuanTriVien::query()->findOrFail($id);

            $this->ensureCanManageMasterAccount($request, $quanTriVien);

            $quanTriVien->delete();

            return $this->successResponse(null, 'Xóa quản trị viên thành công.');
        });
    }

    protected function hydratePermissionData(Collection $collection): void
    {
        $collection->transform(function (QuanTriVien $quanTriVien) {
            $quanTriVien->setAttribute(
                'quyen_ids',
                $quanTriVien->quyenQuanTriViens->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
            );
            $quanTriVien->setAttribute(
                'ten_quyens',
                $quanTriVien->quyenQuanTriViens->pluck('ten_quyen')->values()->all()
            );

            return $quanTriVien;
        });
    }

    protected function ensureCanManageMasterAccount(Request $request, QuanTriVien $quanTriVien): void
    {
        $currentAdmin = $request->user();

        if ((int) $quanTriVien->is_master !== 1) {
            return;
        }

        if (! $currentAdmin || (int) $currentAdmin->is_master !== 1) {
            throw new ApiRequestException('Chỉ tài khoản master mới có quyền sửa hoặc xóa tài khoản master.');
        }
    }

    protected function ensureCanAssignMaster(Request $request, array $payload): void
    {
        if (! array_key_exists('is_master', $payload) || (int) $payload['is_master'] !== 1) {
            return;
        }

        $currentAdmin = $request->user();

        if (! $currentAdmin || (int) $currentAdmin->is_master !== 1) {
            throw new ApiRequestException('Chỉ tài khoản master mới có quyền cấp quyền master.');
        }
    }

    protected function ensureCanManagePermissions(Request $request, array $payload): void
    {
        if (! array_key_exists('quyen_ids', $payload) || ! is_array($payload['quyen_ids'])) {
            return;
        }

        $currentAdmin = $request->user();

        if (! $currentAdmin || (int) $currentAdmin->is_master !== 1) {
            throw new ApiRequestException('Chỉ tài khoản master mới có quyền phân quyền cho quản trị viên.');
        }
    }
}