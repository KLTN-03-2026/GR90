<?php

namespace App\Services\RouteGraph;

/**
 * Đại diện cho một trạm xe buýt trong đồ thị.
 * Đây là DTO (Data Transfer Object), không phải Eloquent Model.
 */
class StopNode
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly string $address,
        public readonly ?float $lat,
        public readonly ?float $lng,
        public readonly array  $routeIds = [],
    ) {}

    /**
     * Tạo StopNode từ model TramXe Eloquent.
     */
    public static function fromTramXe(\App\Models\TramXe $tramXe, array $routeIds = []): self
    {
        return new self(
            id:      $tramXe->id,
            name:    $tramXe->ten_tram,
            address: $tramXe->dia_chi,
            lat:     $tramXe->vi_do ? (float) $tramXe->vi_do : null,
            lng:     $tramXe->kinh_do ? (float) $tramXe->kinh_do : null,
            routeIds: $routeIds,
        );
    }

    /**
     * Chuyển thành mảng JSON.
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'ten_tram' => $this->name,
            'dia_chi'  => $this->address,
            'vi_do'    => $this->lat,
            'kinh_do'  => $this->lng,
        ];
    }
}
