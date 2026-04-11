<?php

namespace App\Services\RouteGraph;

use App\Models\ChiTietLoTrinh;
use App\Models\LoTrinhTuyen;
use App\Models\TramXe;
use App\Models\TuyenXe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Xây dựng đồ thị tuyến xe buýt từ cơ sở dữ liệu.
 *
 * Đồ thị bao gồm:
 * - Nodes: Các trạm xe buýt (StopNode)
 * - Edges: Các cạnh có hướng giữa 2 trạm liền kề trên cùng tuyến (RouteEdge)
 *
 * Đồ thị được cache lại trong 1 giờ để tránh truy vấn DB nhiều lần.
 */
class RouteGraphBuilder
{
    private const CACHE_KEY_GRAPH = 'route_graph_v2';
    private const CACHE_TTL_HOURS = 1;

    /**
     * Toàn bộ đồ thị dưới dạng mảng (đã serialize).
     */
    private array $nodes = [];      // stopId => StopNode data
    private array $edges = [];       // stopId => [RouteEdge data, ...]
    private array $stopRoutes = [];  // stopId => [routeId => routeName]

    /**
     * Xây đồ thị từ DB (có cache).
     */
    public function build(): array
    {
        $cached = Cache::get(self::CACHE_KEY_GRAPH);
        if ($cached !== null) {
            return $cached;
        }

        $this->buildFromDatabase();

        $graph = [
            'nodes'      => $this->nodes,
            'edges'      => $this->edges,
            'stopRoutes' => $this->stopRoutes,
        ];

        Cache::put(self::CACHE_KEY_GRAPH, $graph, now()->addHours(self::CACHE_TTL_HOURS));

        return $graph;
    }

    /**
     * Xây đồ thị từ database.
     */
    public function buildFromDatabase(): void
    {
        $tuyenxes = TuyenXe::query()
            ->with([
                'loTrinhTuyens' => function ($query) {
                    $query->with([
                        'chiTietLoTrinhs' => function ($detailQuery) {
                            $detailQuery
                                ->with('tramXe')
                                ->orderBy('thu_tu_dung');
                        },
                    ])->orderBy('id');
                },
            ])
            ->get();

        $this->nodes = [];
        $this->edges = [];
        $this->stopRoutes = [];

        foreach ($tuyenxes as $tuyenXe) {
            $routeId   = $tuyenXe->id;
            $routeName = $tuyenXe->ten_tuyen;

            foreach ($tuyenXe->loTrinhTuyens as $loTrinh) {
                $direction = $loTrinh->chieu;
                $chiTietList = collect($loTrinh->chiTietLoTrinhs ?? [])->values()->all();

                for ($i = 0; $i < count($chiTietList) - 1; $i++) {
                    $from = $chiTietList[$i];
                    $to   = $chiTietList[$i + 1];

                    $fromTram = $from->tramXe;
                    $toTram   = $to->tramXe;

                    if (! $fromTram || ! $toTram) {
                        continue;
                    }

                    // Thêm node cho trạm đi
                    $this->addNode($fromTram, $routeId, $routeName);
                    // Thêm node cho trạm đến
                    $this->addNode($toTram, $routeId, $routeName);

                    // Thêm cạnh từ from -> to
                    $this->addEdge($from, $to, $tuyenXe, $loTrinh, $direction);
                }

                // Trường hợp chỉ có 1 trạm duy nhất
                if (count($chiTietList) === 1) {
                    $tram = $chiTietList[0]->tramXe;
                    if ($tram) {
                        $this->addNode($tram, $routeId, $routeName);
                    }
                }
            }
        }
    }

    /**
     * Thêm một node (trạm) vào đồ thị.
     */
    private function addNode(TramXe $tramXe, int $routeId, string $routeName): void
    {
        $stopId = $tramXe->id;

        // Khởi tạo node nếu chưa có
        if (! isset($this->nodes[$stopId])) {
            $this->nodes[$stopId] = [
                'id'        => $stopId,
                'name'      => $tramXe->ten_tram,
                'address'   => $tramXe->dia_chi,
                'lat'       => $tramXe->vi_do ? (float) $tramXe->vi_do : null,
                'lng'       => $tramXe->kinh_do ? (float) $tramXe->kinh_do : null,
            ];
        }

        // Ghi nhận tuyến đi qua trạm này
        if (! isset($this->stopRoutes[$stopId])) {
            $this->stopRoutes[$stopId] = [];
        }
        $this->stopRoutes[$stopId][$routeId] = $routeName;
    }

    /**
     * Thêm một cạnh vào đồ thị.
     */
    private function addEdge(
        ChiTietLoTrinh $from,
        ChiTietLoTrinh $to,
        TuyenXe $tuyenXe,
        LoTrinhTuyen $loTrinh,
        string $direction
    ): void {
        $fromTram = $from->tramXe;
        $toTram   = $to->tramXe;

        $edgeData = [
            'route_id'     => $tuyenXe->id,
            'route_name'  => $tuyenXe->ten_tuyen,
            'direction'   => $direction,
            'from_stop_id'   => $fromTram->id,
            'from_stop_name' => $fromTram->ten_tram,
            'to_stop_id'     => $toTram->id,
            'to_stop_name'   => $toTram->ten_tram,
            'from_order'   => $from->thu_tu_dung,
            'to_order'     => $to->thu_tu_dung,
            'duration_sec' => (int) ($from->thoi_gian_dung_du_kien_giay ?? 0),
            'distance_m'   => (float) ($from->khoang_cach_tu_diem_truoc_met ?? 0),
        ];

        if (! isset($this->edges[$fromTram->id])) {
            $this->edges[$fromTram->id] = [];
        }

        $this->edges[$fromTram->id][] = $edgeData;
    }

    /**
     * Xóa cache đồ thị (gọi khi dữ liệu tuyến xe thay đổi).
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_GRAPH);
    }

    /**
     * Lấy danh sách tất cả stop ID.
     */
    public function getAllStopIds(array $graph): array
    {
        return array_keys($graph['nodes']);
    }

    /**
     * Lấy node theo stop ID.
     */
    public function getNode(array $graph, int $stopId): ?array
    {
        return $graph['nodes'][$stopId] ?? null;
    }

    /**
     * Lấy các cạnh xuất phát từ một trạm.
     */
    public function getEdgesFrom(array $graph, int $stopId): array
    {
        return $graph['edges'][$stopId] ?? [];
    }

    /**
     * Lấy danh sách tuyến đi qua một trạm.
     */
    public function getRoutesForStop(array $graph, int $stopId): array
    {
        return $graph['stopRoutes'][$stopId] ?? [];
    }
}
