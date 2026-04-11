<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TrangThaiTuyenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trangThaiTuyens = [
            [
                'ma_trang_thai' => 'DN_TT_DANG_HOAT_DONG',
                'ten_trang_thai' => 'Đang hoạt động',
            ],
            [
                'ma_trang_thai' => 'DN_TT_TAM_DUNG',
                'ten_trang_thai' => 'Tạm dừng',
            ],
            [
                'ma_trang_thai' => 'DN_TT_THU_TUC_VAN_HANH',
                'ten_trang_thai' => 'Đang làm thủ tục vận hành',
            ],
        ];

        foreach ($trangThaiTuyens as $item) {
            DB::table('trang_thai_tuyens')->updateOrInsert(
                ['ma_trang_thai' => $item['ma_trang_thai']],
                [
                    'ten_trang_thai' => $item['ten_trang_thai'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
