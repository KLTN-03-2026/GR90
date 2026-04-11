<?php

namespace Database\Seeders;

use App\Models\LoTrinhTuyen;
use App\Models\TuyenXe;
use Illuminate\Database\Seeder;

class LoTrinhTuyenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dữ liệu hướng tuyến dựa trên thông tin điểm đầu/điểm cuối từ Danangbus.
        TuyenXe::query()->select(['id', 'diem_dau', 'diem_cuoi'])->chunkById(100, function ($tuyenXes): void {
            foreach ($tuyenXes as $tuyenXe) {
                $diemDau = $tuyenXe->diem_dau ?: 'Điểm đầu chưa cập nhật';
                $diemCuoi = $tuyenXe->diem_cuoi ?: 'Điểm cuối chưa cập nhật';

                LoTrinhTuyen::query()->updateOrCreate(
                    [
                        'tuyen_xe_id' => $tuyenXe->id,
                        'chieu' => 'di',
                    ],
                    [
                        'mo_ta_lo_trinh' => $diemDau . ' -> ' . $diemCuoi,
                    ]
                );

                LoTrinhTuyen::query()->updateOrCreate(
                    [
                        'tuyen_xe_id' => $tuyenXe->id,
                        'chieu' => 've',
                    ],
                    [
                        'mo_ta_lo_trinh' => $diemCuoi . ' -> ' . $diemDau,
                    ]
                );
            }
        });
    }
}
