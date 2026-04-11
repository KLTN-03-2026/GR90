<?php

namespace Database\Seeders;

use App\Models\DonViVanHanh;
use Illuminate\Database\Seeder;

class DonViVanHanhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nguồn: https://www.danangbus.vn/lo-trinh-tuyen.html
        // Chỉ insert các đơn vị vận hành được nêu rõ ràng trên Danangbus.
        $donVis = [
            [
                'ma_don_vi' => 'DN_DV_PT',
                'ten_don_vi' => 'Công ty Cổ phần Xe khách Phương Trang - FUTAbuslines',
            ],
            [
                'ma_don_vi' => 'DN_DV_QA1',
                'ten_don_vi' => 'Công ty Cổ phần Công nghiệp Quảng An 1',
            ],
        ];

        foreach ($donVis as $item) {
            DonViVanHanh::query()->updateOrCreate(
                ['ma_don_vi' => $item['ma_don_vi']],
                [
                    'ten_don_vi' => $item['ten_don_vi'],
                    'dia_chi' => null,
                    'so_dien_thoai' => null,
                    'email' => null,
                ]
            );
        }
    }
}
