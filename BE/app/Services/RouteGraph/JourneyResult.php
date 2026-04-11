<?php

namespace App\Services\RouteGraph;

/**
 * Đại diện cho một hành trình hoàn chỉnh từ điểm đi đến điểm đến.
 * Có thể là tuyến đơn hoặc tuyến kết hợp (multi-hop).
 */
class JourneyResult
{
    public function __construct(
        public readonly string $type,       // 'direct' | 'one_transfer' | 'two_transfers'
        public readonly int    $totalTransfers,
        public readonly array   $legs,       // JourneyLeg[]
    ) {}

    /**
     * Tổng thời gian di chuyển (giây).
     */
    public function totalDurationSec(): int
    {
        return collect($this->legs)->sum(fn (JourneyLeg $leg) => $leg->durationSec);
    }

    /**
     * Tổng khoảng cách (mét).
     */
    public function totalDistanceM(): float
    {
        return collect($this->legs)->sum(fn (JourneyLeg $leg) => $leg->distanceM);
    }

    /**
     * Chuyển thành mảng JSON.
     */
    public function toArray(): array
    {
        return [
            'type'              => $this->type,
            'total_transfers'   => $this->totalTransfers,
            'total_duration_min' => (int) ceil($this->totalDurationSec() / 60),
            'total_distance_km' => round($this->totalDistanceM() / 1000, 1),
            'legs'              => collect($this->legs)->map(fn (JourneyLeg $leg) => $leg->toArray())->values()->all(),
        ];
    }

    /**
     * Tạo JourneyResult cho tuyến đơn (không chuyển tuyến).
     */
    public static function direct(JourneyLeg $leg): self
    {
        return new self(type: 'direct', totalTransfers: 0, legs: [$leg]);
    }

    /**
     * Tạo JourneyResult cho tuyến kết hợp 1 chuyển tuyến.
     */
    public static function oneTransfer(JourneyLeg $leg1, JourneyLeg $leg2): self
    {
        return new self(type: 'one_transfer', totalTransfers: 1, legs: [$leg1, $leg2]);
    }

    /**
     * Tạo JourneyResult cho tuyến kết hợp 2 chuyển tuyến.
     */
    public static function twoTransfers(JourneyLeg $leg1, JourneyLeg $leg2, JourneyLeg $leg3): self
    {
        return new self(type: 'two_transfers', totalTransfers: 2, legs: [$leg1, $leg2, $leg3]);
    }
}
