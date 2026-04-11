<?php

namespace App\Services\RouteGraph;

use Illuminate\Support\Facades\Cache;

/**
 * Service chính để tìm tuyến kết hợp (multi-hop).
 * Cung cấp API đơn giản cho Controller.
 *
 * Luồng xử lý:
 * 1. Xây đồ thị từ DB (có cache)
 * 2. Chạy BFS tìm các tuyến đơn và kết hợp
 * 3. Trả về kết quả đã định dạng
 */
class RouteGraphService
{
    private RouteGraphBuilder $builder;
    private BfsRouteFinder $finder;

    public function __construct()
    {
        $this->builder = new RouteGraphBuilder;
        $this->finder = new BfsRouteFinder($this->builder);
    }

    /**
     * Tìm tuyến kết hợp từ điểm đi đến điểm đến.
     *
     * @param  string $diemDi   Tên/mô tả điểm đi
     * @param  string $diemDen  Tên/mô tả điểm đến
     * @param  string $tuKhoa   Từ khóa tuyến (tùy chọn)
     * @param  int    $limit    Số kết quả tối đa
     * @return array Kết quả đã định dạng JSON
     */
    public function findRoutes(string $diemDi, string $diemDen, string $tuKhoa = '', int $limit = 8): array
    {
        $journeys = $this->finder->find($diemDi, $diemDen, $tuKhoa, $limit);

        return [
            'filters' => [
                'diem_di'   => $diemDi,
                'diem_den'  => $diemDen,
                'tu_khoa'   => $tuKhoa,
            ],
            'items' => collect($journeys)->map(fn (JourneyResult $j) => $j->toArray())->values()->all(),
        ];
    }

    /**
     * Trả về danh sách stop ID có thể đến từ một stop (theo chiều đi).
     * Dùng để lọc điểm đến gợi ý khi đã chọn điểm đi.
     *
     * @param  int  $fromStopId  Stop ID bắt đầu
     * @param  int  $maxHops     Số lần chuyển tối đa (mặc định 2)
     * @return int[]             Danh sách stop ID đến được
     */
    public function getReachableStops(int $fromStopId, int $maxHops = 2): array
    {
        $graph = $this->builder->build();

        if (! isset($graph['nodes'][$fromStopId])) {
            return [];
        }

        $reachable = [];
        $visited   = [];
        $queue     = [[
            'stopId'  => $fromStopId,
            'routeId' => null,
            'hops'    => 0,
        ]];

        while (! empty($queue)) {
            $state = array_shift($queue);

            $edges = $this->builder->getEdgesFrom($graph, $state['stopId']);

            foreach ($edges as $edge) {
                $nextId    = $edge['to_stop_id'];
                $nextRoute = $edge['route_id'];

                if (! isset($visited[$nextId][$nextRoute])) {
                    $visited[$nextId][$nextRoute] = true;
                } else {
                    continue;
                }

                $isNewTransfer = $state['routeId'] !== null && $state['routeId'] !== $nextRoute;
                $newHops = $isNewTransfer
                    ? $state['hops'] + 1
                    : $state['hops'];

                if ($newHops > $maxHops) {
                    continue;
                }

                if ($nextId !== $fromStopId) {
                    $reachable[$nextId] = true;
                }

                $queue[] = [
                    'stopId'  => $nextId,
                    'routeId' => $nextRoute,
                    'hops'    => $newHops,
                ];
            }
        }

        return array_keys($reachable);
    }

    /**
     * Xóa cache đồ thị.
     * Gọi khi dữ liệu tuyến xe thay đổi (thêm/sửa/xóa tuyến, lộ trình, trạm).
     */
    public function clearGraphCache(): void
    {
        $this->builder->clearCache();
    }

    /**
     * Lấy thông tin đồ thị để debug.
     */
    public function getGraphInfo(): array
    {
        $graph = $this->builder->build();

        return [
            'total_nodes'    => count($graph['nodes']),
            'total_edges'    => collect($graph['edges'])->sum(fn ($edges) => count($edges)),
            'total_stop_routes' => count($graph['stopRoutes']),
        ];
    }
}
