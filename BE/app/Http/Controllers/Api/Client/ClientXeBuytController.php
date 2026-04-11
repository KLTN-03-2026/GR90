<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\XeBuytSimulationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ClientXeBuytController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly XeBuytSimulationService $simulationService
    ) {}

    /**
     * GET /api/client/routes/{id}/buses
     * Danh sách xe buýt đang hoạt động trên một tuyến.
     */
    public function byRoute(Request $request, string $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $buses = $this->simulationService->getBusesByRoute((int) $id);

            return $this->dataResponse([
                'tuyen_xe_id' => (int) $id,
                'tong_xe' => count($buses),
                'xe_buyts' => $buses,
            ], 'Lay danh sach xe buyt theo tuyen thanh cong.');
        });
    }

    /**
     * GET /api/client/stops/{id}/buses
     * Danh sách xe buýt sắp đến một trạm (kèm ETA).
     */
    public function byStop(Request $request, string $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $buses = $this->simulationService->getActiveBusesByStop((int) $id);

            return $this->dataResponse([
                'tram_xe_id' => (int) $id,
                'tong_xe' => count($buses),
                'xe_buyts' => $buses,
            ], 'Lay danh sach xe buyt gan tram thanh cong.');
        });
    }

    /**
     * GET /api/client/routes/{id}/schedule
     * Biểu giờ hoạt động chi tiết của một tuyến.
     */
    public function schedule(Request $request, string $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $schedule = $this->simulationService->getScheduleByRoute((int) $id);

            return $this->dataResponse($schedule, 'Lay lich hoat dong tuyen xe thanh cong.');
        });
    }

    /**
     * GET /api/client/buses/live
     * Tổng hợp vị trí tất cả xe buýt đang hoạt động (dùng cho bản đồ).
     */
    public function liveAll(Request $request): JsonResponse
    {
        return $this->handle(function () {
            $routeIds = \App\Models\XeBuyt::query()
                ->where('trang_thai', 'dang_hoat_dong')
                ->pluck('tuyen_xe_id')
                ->unique()
                ->values()
                ->all();

            $allBuses = [];
            foreach ($routeIds as $tuyenXeId) {
                $buses = $this->simulationService->getActiveBusesByRoute($tuyenXeId);
                foreach ($buses as $bus) {
                    $bus['tuyen_xe_id'] = $tuyenXeId;
                    $allBuses[] = $bus;
                }
            }

            return $this->dataResponse([
                'tong_xe' => count($allBuses),
                'xe_buyts' => $allBuses,
                'thoi_gian_cap_nhat' => now()->toIso8601String(),
            ], 'Lay vi tri xe buyt thanh cong.');
        });
    }

    private function handle(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (\App\Exceptions\ApiRequestException $exception) {
            throw $exception;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            throw new \App\Exceptions\ApiRequestException('Khong tim thay du lieu can thao tac.');
        } catch (Throwable $exception) {
            throw new \App\Exceptions\ApiRequestException('Thao tac that bai.', [
                'chi_tiet' => $exception->getMessage(),
            ]);
        }
    }
}
