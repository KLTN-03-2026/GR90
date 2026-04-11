<?php

namespace App\Services\RouteGraph;

/**
 * Đại diện cho một cạnh (đoạn đường) trong đồ thị tuyến xe.
 * Mỗi RouteEdge biểu diễn việc di chuyển từ trạm này đến trạm kế tiếp trên cùng một tuyến.
 * Đây là DTO, không phải Eloquent Model.
 */
class RouteEdge
{
    public function __construct(
        public readonly int    $routeId,
        public readonly string $routeName,
        public readonly string $direction,
        public readonly int    $fromStopId,
        public readonly string $fromStopName,
        public readonly int    $toStopId,
        public readonly string $toStopName,
        public readonly int    $fromOrder,
        public readonly int    $toOrder,
        public readonly int    $durationSec = 0,
        public readonly float  $distanceM = 0.0,
    ) {}

    /**
     * Tạo RouteEdge từ chi tiết lộ trình.
     */
    public static function fromChiTietLoTrinh(
        \App\Models\ChiTietLoTrinh $chiTiet,
        string $routeName,
    ): self {
        $tramXe = $chiTiet->tramXe;
        $loTrinh = $chiTiet->loTrinhTuyen;
        $tuyenXe = $loTrinh->tuyenXe;

        return new self(
            routeId:     $tuyenXe->id,
            routeName:  $routeName,
            direction:  $loTrinh->chieu,
            fromStopId: $tramXe->id,
            fromStopName: $tramXe->ten_tram,
            toStopId:   $tramXe->id,
            toStopName: $tramXe->ten_tram,
            fromOrder:  $chiTiet->thu_tu_dung,
            toOrder:    $chiTiet->thu_tu_dung,
            durationSec: (int) $chiTiet->thoi_gian_dung_du_kien_giay,
            distanceM:   (float) $chiTiet->khoang_cach_tu_diem_truoc_met,
        );
    }

    /**
     * Mã định danh duy nhất cho cạnh này.
     */
    public function key(): string
    {
        return "{$this->routeId}:{$this->direction}:{$this->fromStopId}";
    }
}
