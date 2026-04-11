<?php

namespace App\Services\RouteGraph;

/**
 * Đại diện cho một bước đi trong hành trình (một chặng trên một tuyến).
 * Ví dụ: Đi tuyến DNR01 từ "Bến Xe Trung Tâm" đến "Trạm Sân Bay".
 */
class JourneyLeg
{
    /**
     * @param int    $routeId
     * @param string $routeName
     * @param string $direction
     * @param int    $fromStopId
     * @param string $fromStopName
     * @param int    $toStopId
     * @param string $toStopName
     * @param array  $intermediateStops Mảng các StopNode trung gian
     * @param int    $durationSec
     * @param float  $distanceM
     */
    public function __construct(
        public readonly int    $routeId,
        public readonly string $routeName,
        public readonly string $direction,
        public readonly int    $fromStopId,
        public readonly string $fromStopName,
        public readonly int    $toStopId,
        public readonly string $toStopName,
        public readonly array   $intermediateStops = [],
        public readonly int    $durationSec = 0,
        public readonly float  $distanceM = 0.0,
    ) {}

    /**
     * Chuyển thành mảng JSON.
     */
    public function toArray(): array
    {
        return [
            'route_id'   => $this->routeId,
            'route_name' => $this->routeName,
            'direction' => $this->direction,
            'from_stop' => [
                'id'   => $this->fromStopId,
                'name' => $this->fromStopName,
            ],
            'to_stop' => [
                'id'   => $this->toStopId,
                'name' => $this->toStopName,
            ],
            'intermediate_stops' => collect($this->intermediateStops)->map(fn (StopNode $stop) => $stop->toArray())->values()->all(),
            'duration_min'   => (int) ceil($this->durationSec / 60),
            'distance_km'    => round($this->distanceM / 1000, 1),
        ];
    }
}
