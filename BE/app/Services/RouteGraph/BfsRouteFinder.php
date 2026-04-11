<?php

namespace App\Services\RouteGraph;

use App\Models\TramXe;
use Illuminate\Support\Collection;

/**
 * Thuật toán BFS tìm tuyến kết hợp (multi-hop route finding).
 *
 * Thuật toán:
 * 1. Tìm tất cả trạm khớp với điểm đi và điểm đến
 * 2. BFS từ các trạm điểm đi, đi theo cạnh đồ thị
 * 3. Mỗi lần chuyển tuyến = tăng transfer count
 * 4. Khi đến trạm đích → ghi nhận một hành trình
 * 5. Sắp xếp theo số chuyển tuyến ASC, khoảng cách ASC
 */
class BfsRouteFinder
{
    private RouteGraphBuilder $builder;
    private array $graph;

    /** @var int Giới hạn kết quả trả về */
    private int $limit = 8;

    /** @var int Số chuyển tuyến tối đa cho phép */
    private int $maxTransfers = 2;

    /** @var array Danh sách stop ID của điểm đi */
    private array $startStopIds = [];

    /** @var array Danh sách stop ID của điểm đến */
    private array $endStopIds = [];

    public function __construct(RouteGraphBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Tìm các tuyến kết hợp từ điểm đi đến điểm đến.
     *
     * @param  string $diemDi   Tên/mô tả điểm đi
     * @param  string $diemDen  Tên/mô tả điểm đến
     * @param  string $tuKhoa   Từ khóa tuyến (tùy chọn)
     * @param  int    $limit    Số kết quả tối đa
     * @return JourneyResult[]
     */
    public function find(string $diemDi, string $diemDen, string $tuKhoa = '', int $limit = 8): array
    {
        $this->graph = $this->builder->build();
        $this->limit = $limit;

        // Bước 1: Tìm các trạm khớp với điểm đi
        $this->startStopIds = $this->findMatchedStopIds($diemDi);

        // Bước 2: Tìm các trạm khớp với điểm đến
        $this->endStopIds = $this->findMatchedStopIds($diemDen);

        // Nếu không tìm được trạm nào
        if (empty($this->startStopIds) || empty($this->endStopIds)) {
            return [];
        }

        // Bước 3: BFS tìm đường đi
        $directJourneys = $this->findDirectRoutes($tuKhoa);
        $oneTransferJourneys = $this->findMultiHopRoutes(1, $tuKhoa);

        // Ghép kết quả và sắp xếp
        $allJourneys = array_merge($directJourneys, $oneTransferJourneys);

        usort($allJourneys, function (JourneyResult $a, JourneyResult $b) {
            // Ưu tiên ít chuyển tuyến hơn
            if ($a->totalTransfers !== $b->totalTransfers) {
                return $a->totalTransfers <=> $b->totalTransfers;
            }
            // Sau đó ưu tiên khoảng cách ngắn hơn
            return $a->totalDistanceM() <=> $b->totalDistanceM();
        });

        return array_slice($allJourneys, 0, $this->limit);
    }

    /**
     * Tìm các tuyến trực tiếp (không chuyển tuyến).
     */
    private function findDirectRoutes(string $tuKhoa = ''): array
    {
        $journeys = [];
        $usedRoutes = [];

        foreach ($this->startStopIds as $fromStopId) {
            $edges = $this->builder->getEdgesFrom($this->graph, $fromStopId);

            foreach ($edges as $edge) {
                $routeId = $edge['route_id'];

                // Nếu đã tìm tuyến này rồi thì bỏ qua
                if (isset($usedRoutes[$routeId])) {
                    continue;
                }

                // Lọc theo từ khóa nếu có
                if ($tuKhoa !== '' && ! $this->routeMatchesKeyword($edge['route_name'], $tuKhoa)) {
                    continue;
                }

                // Xây dựng chặng đi từ startStop đến hết tuyến này
                $leg = $this->buildLegAlongRoute($fromStopId, $routeId, $edge['direction']);

                if ($leg === null) {
                    continue;
                }

                // Kiểm tra xem có đến được điểm đích không
                if (in_array($leg->toStopId, $this->endStopIds)) {
                    $journeys[] = JourneyResult::direct($leg);
                    $usedRoutes[$routeId] = true;

                    if (count($journeys) >= $this->limit) {
                        return $journeys;
                    }
                }
            }
        }

        return $journeys;
    }

    /**
     * Tìm tuyến kết hợp với N chuyển tuyến.
     */
    private function findMultiHopRoutes(int $maxTransfers, string $tuKhoa = ''): array
    {
        $journeys = [];
        $visited = [];

        /**
         * State BFS: lưu trạng thái hiện tại của mỗi bước tìm kiếm.
         * [
         *   'currentStopId' => int,
         *   'routeId'       => int (tuyến hiện tại đang đi),
         *   'legs'          => JourneyLeg[],
         *   'transfers'     => int,
         *   'visitedStops'  => int[],
         * ]
         */
        $queue = [];

        // Khởi tạo: bắt đầu từ mỗi startStop với mỗi tuyến đi qua nó
        foreach ($this->startStopIds as $startStopId) {
            $routes = $this->builder->getRoutesForStop($this->graph, $startStopId);

            foreach ($routes as $routeId => $routeName) {
                if ($tuKhoa !== '' && ! $this->routeMatchesKeyword($routeName, $tuKhoa)) {
                    continue;
                }

                // Tạo cạnh đầu tiên
                $edges = $this->builder->getEdgesFrom($this->graph, $startStopId);
                $firstEdge = collect($edges)->first(fn ($e) => $e['route_id'] === $routeId);

                if (! $firstEdge) {
                    continue;
                }

                $leg = $this->buildLegFromEdge($firstEdge, $startStopId);

                if ($leg === null) {
                    continue;
                }

                $queue[] = [
                    'currentStopId' => $firstEdge['to_stop_id'],
                    'routeId'       => $routeId,
                    'legs'          => [$leg],
                    'transfers'     => 0,
                    'visitedStops'  => [$startStopId],
                ];
            }
        }

        // BFS loop
        $iterations = 0;
        $maxIterations = 10000;

        while (! empty($queue) && $iterations < $maxIterations) {
            $iterations++;
            $state = array_shift($queue);

            // Đã đến đích chưa?
            if (in_array($state['currentStopId'], $this->endStopIds)) {
                // Ghép các legs thành hành trình
                $journey = $this->buildJourneyFromLegs($state['legs'], $state['transfers']);
                if ($journey !== null) {
                    $journeys[] = $journey;
                    if (count($journeys) >= $this->limit) {
                        return $journeys;
                    }
                }
                continue;
            }

            // Đã đạt giới hạn chuyển tuyến
            if ($state['transfers'] >= $maxTransfers) {
                continue;
            }

            // Lấy các cạnh đi ra từ trạm hiện tại
            $edges = $this->builder->getEdgesFrom($this->graph, $state['currentStopId']);

            foreach ($edges as $edge) {
                $nextStopId = $edge['to_stop_id'];

                // Đã đến đích?
                if (in_array($nextStopId, $this->endStopIds)) {
                    // Ghép legs + thêm chặng cuối
                    $newLegs = $state['legs'];
                    $finalLeg = $this->buildLegToStop($state['legs'][count($state['legs']) - 1], $nextStopId, $edge);
                    if ($finalLeg !== null) {
                        $newLegs[] = $finalLeg;
                        $newTransfers = $edge['route_id'] !== $state['routeId']
                            ? $state['transfers'] + 1
                            : $state['transfers'];
                        $journey = $this->buildJourneyFromLegs($newLegs, $newTransfers);
                        if ($journey !== null) {
                            $journeys[] = $journey;
                            if (count($journeys) >= $this->limit) {
                                return $journeys;
                            }
                        }
                    }
                    continue;
                }

                // Tránh đi vòng: không quay lại trạm đã đi
                if (in_array($nextStopId, $state['visitedStops'])) {
                    continue;
                }

                // Tạo visited key
                $visitKey = "{$nextStopId}:{$edge['route_id']}";
                $transferKey = "{$nextStopId}:{$edge['route_id']}:{$state['transfers']}";

                if (isset($visited[$transferKey])) {
                    continue;
                }
                $visited[$transferKey] = true;

                $isNewTransfer = $edge['route_id'] !== $state['routeId'];
                $newTransfers = $isNewTransfer ? $state['transfers'] + 1 : $state['transfers'];

                // Không vượt quá giới hạn
                if ($newTransfers > $maxTransfers) {
                    continue;
                }

                $newLegs = $state['legs'];

                if ($isNewTransfer) {
                    // Chuyển tuyến mới: bắt đầu leg mới
                    $newLeg = $this->buildLegFromEdge($edge, $state['currentStopId']);
                    if ($newLeg !== null) {
                        $newLegs[] = $newLeg;
                    } else {
                        continue;
                    }
                } else {
                    // Tiếp tục tuyến cũ: mở rộng leg cuối cùng
                    $lastIndex = count($newLegs) - 1;
                    $lastLeg = $newLegs[$lastIndex];

                    // Cập nhật điểm đến của leg cuối
                    $newLegs[$lastIndex] = $this->extendLeg($lastLeg, $edge, $nextStopId);
                }

                $queue[] = [
                    'currentStopId' => $nextStopId,
                    'routeId'       => $edge['route_id'],
                    'legs'          => $newLegs,
                    'transfers'     => $newTransfers,
                    'visitedStops'  => [...$state['visitedStops'], $nextStopId],
                ];
            }
        }

        return $journeys;
    }

    /**
     * Tìm các stop ID khớp với từ khóa.
     * Hỗ trợ cả tìm theo tên trạm lẫn theo stop ID trực tiếp.
     */
    private function findMatchedStopIds(string $keyword): array
    {
        if ($keyword === '') {
            return [];
        }

        $matchedIds = [];
        $normalizedKeyword = mb_strtolower(trim($keyword));

        // Thử parse thành ID số nếu là ID
        if (is_numeric($keyword)) {
            $id = (int) $keyword;
            if (isset($this->graph['nodes'][$id])) {
                return [$id];
            }
        }

        // Tìm kiếm mở rộng: từ khóa nằm trong tên trạm
        // Không yêu cầu khớp toàn bộ - chỉ cần từ khóa xuất hiện
        foreach ($this->graph['nodes'] as $stopId => $node) {
            $nodeName    = mb_strtolower($node['name']);
            $nodeAddress = mb_strtolower($node['address']);

            // Tìm fuzzy: từ khóa là substring của tên trạm
            if (str_contains($nodeName, $normalizedKeyword) || str_contains($nodeAddress, $normalizedKeyword)) {
                $matchedIds[] = $stopId;
                continue;
            }

            // Tìm ngược: tên trạm là substring của từ khóa
            if (str_contains($normalizedKeyword, $nodeName) && mb_strlen($nodeName) >= 5) {
                $matchedIds[] = $stopId;
            }
        }

        return $matchedIds;
    }

    /**
     * Kiểm tra tuyến có khớp với từ khóa không.
     */
    private function routeMatchesKeyword(string $routeName, string $keyword): bool
    {
        return str_contains(mb_strtolower($routeName), mb_strtolower($keyword));
    }

    /**
     * Xây dựng một leg đi dọc theo tuyến từ startStop đến cuối tuyến.
     */
    private function buildLegAlongRoute(int $startStopId, int $routeId, string $direction): ?JourneyLeg
    {
        $startNode = $this->builder->getNode($this->graph, $startStopId);
        if (! $startNode) {
            return null;
        }

        $edges = $this->builder->getEdgesFrom($this->graph, $startStopId);
        $routeEdges = collect($edges)->filter(fn ($e) => $e['route_id'] === $routeId && $e['direction'] === $direction)->values()->all();

        if (empty($routeEdges)) {
            return null;
        }

        $lastEdge = end($routeEdges);

        // Thu thập các trạm trung gian
        $intermediateStops = [];
        $totalDuration = 0;
        $totalDistance = 0.0;

        foreach ($routeEdges as $e) {
            $intermediateStops[] = new StopNode(
                id:      $e['to_stop_id'],
                name:    $e['to_stop_name'],
                address: '',
                lat:     null,
                lng:     null,
            );
            $totalDuration += $e['duration_sec'];
            $totalDistance += $e['distance_m'];
        }

        return new JourneyLeg(
            routeId:     $routeId,
            routeName:  $lastEdge['route_name'],
            direction:  $direction,
            fromStopId: $startStopId,
            fromStopName: $startNode['name'],
            toStopId:   $lastEdge['to_stop_id'],
            toStopName: $lastEdge['to_stop_name'],
            intermediateStops: $intermediateStops,
            durationSec: $totalDuration,
            distanceM:   $totalDistance,
        );
    }

    /**
     * Xây dựng leg từ một cạnh (dùng khi bắt đầu hoặc chuyển tuyến).
     */
    private function buildLegFromEdge(array $edge, int $fromStopId): ?JourneyLeg
    {
        $fromNode = $this->builder->getNode($this->graph, $fromStopId);
        if (! $fromNode) {
            return null;
        }

        return new JourneyLeg(
            routeId:     $edge['route_id'],
            routeName:  $edge['route_name'],
            direction:  $edge['direction'],
            fromStopId: $fromStopId,
            fromStopName: $fromNode['name'],
            toStopId:   $edge['to_stop_id'],
            toStopName: $edge['to_stop_name'],
            intermediateStops: [],
            durationSec: $edge['duration_sec'],
            distanceM:   $edge['distance_m'],
        );
    }

    /**
     * Xây dựng leg cuối cùng đến điểm đích.
     */
    private function buildLegToStop(JourneyLeg $currentLeg, int $toStopId, array $edge): ?JourneyLeg
    {
        $toNode = $this->builder->getNode($this->graph, $toStopId);
        if (! $toNode) {
            return null;
        }

        return new JourneyLeg(
            routeId:     $edge['route_id'],
            routeName:  $edge['route_name'],
            direction:  $edge['direction'],
            fromStopId: $currentLeg->toStopId,
            fromStopName: $currentLeg->toStopName,
            toStopId:   $toStopId,
            toStopName: $toNode['name'],
            intermediateStops: [],
            durationSec: $edge['duration_sec'],
            distanceM:   $edge['distance_m'],
        );
    }

    /**
     * Mở rộng một leg hiện tại với cạnh tiếp theo.
     */
    private function extendLeg(JourneyLeg $leg, array $edge, int $nextStopId): JourneyLeg
    {
        $nextNode = $this->builder->getNode($this->graph, $nextStopId);
        $nextStopName = $nextNode['name'] ?? $edge['to_stop_name'];

        return new JourneyLeg(
            routeId:     $leg->routeId,
            routeName:  $leg->routeName,
            direction:  $leg->direction,
            fromStopId: $leg->fromStopId,
            fromStopName: $leg->fromStopName,
            toStopId:   $nextStopId,
            toStopName: $nextStopName,
            intermediateStops: [
                ...$leg->intermediateStops,
                new StopNode(
                    id:      $nextStopId,
                    name:    $nextStopName,
                    address: '',
                    lat:     null,
                    lng:     null,
                ),
            ],
            durationSec: $leg->durationSec + $edge['duration_sec'],
            distanceM:   $leg->distanceM + $edge['distance_m'],
        );
    }

    /**
     * Ghép các legs thành JourneyResult.
     */
    private function buildJourneyFromLegs(array $legs, int $transfers): ?JourneyResult
    {
        if (empty($legs)) {
            return null;
        }

        $type = match (true) {
            $transfers === 0 => 'direct',
            $transfers === 1 => 'one_transfer',
            default => 'two_transfers',
        };

        return new JourneyResult(
            type: $type,
            totalTransfers: $transfers,
            legs: $legs,
        );
    }
}
