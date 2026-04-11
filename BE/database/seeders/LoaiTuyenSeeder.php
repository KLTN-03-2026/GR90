<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class LoaiTuyenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loaiTuyens = [
            [
                'ma_loai_tuyen' => 'DN_LT_TRO_GIA',
                'ten_loai_tuyen' => 'Tuyến buýt có trợ giá',
                'mo_ta' => 'Nhóm tuyến có trợ giá theo danh mục Danangbus.',
            ],
            [
                'ma_loai_tuyen' => 'DN_LT_KHONG_TRO_GIA',
                'ten_loai_tuyen' => 'Tuyến buýt không trợ giá',
                'mo_ta' => 'Nhóm tuyến không trợ giá theo danh mục Danangbus.',
            ],
            [
                'ma_loai_tuyen' => 'DN_LT_LIEN_KE',
                'ten_loai_tuyen' => 'Tuyến buýt liên kề',
                'mo_ta' => 'Nhóm tuyến liên kề như tuyến Đà Nẵng - Huế.',
            ],
            [
                'ma_loai_tuyen' => 'DN_LT_DU_LICH',
                'ten_loai_tuyen' => 'Tuyến buýt du lịch',
                'mo_ta' => 'Nhóm tuyến phục vụ du lịch.',
            ],
        ];

        foreach ($loaiTuyens as $item) {
            DB::table('loai_tuyens')->updateOrInsert(
                ['ma_loai_tuyen' => $item['ma_loai_tuyen']],
                [
                    'ten_loai_tuyen' => $item['ten_loai_tuyen'],
                    'mo_ta' => $item['mo_ta'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
