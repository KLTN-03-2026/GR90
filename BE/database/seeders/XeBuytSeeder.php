<?php

namespace Database\Seeders;

use App\Models\TuyenXe;
use App\Models\XeBuyt;
use Illuminate\Database\Seeder;

class XeBuytSeeder extends Seeder
{
    /**
     * Tạo 2–3 xe buýt cho mỗi tuyến xe.
     */
    public function run(): void
    {
        $tuyenXes = TuyenXe::query()
            ->whereHas('trangThaiTuyen', fn ($q) => $q->where('ma_trang_thai', 'DN_TT_DANG_HOAT_DONG'))
            ->get();

        $loaiXes = ['Xe buýt 40 chỗ', 'Xe buýt 45 chỗ', 'Xe buýt 50 chỗ', 'Xe buýt 55 chỗ', 'Xe buýt 60 chỗ'];
        $trangThaiXes = ['dang_hoat_dong', 'dang_hoat_dong', 'dang_cho'];
        $namHienTai = (int) date('Y');

        foreach ($tuyenXes as $tuyenXe) {
            $soXe = fake()->numberBetween(2, 3);

            for ($i = 1; $i <= $soXe; $i++) {
                $bienSo = $this->taoBienSo($tuyenXe->ma_tuyen, $i);
                $namSX = fake()->numberBetween(max(2018, $namHienTai - 8), $namHienTai);

                XeBuyt::query()->updateOrCreate(
                    ['bien_so' => $bienSo],
                    [
                        'ten_xe' => "Xe {$tuyenXe->ma_tuyen}-" . fake()->numerify('###'),
                        'tuyen_xe_id' => $tuyenXe->id,
                        'trang_thai' => $trangThaiXes[array_rand($trangThaiXes)],
                        'loai_xe' => $loaiXes[array_rand($loaiXes)],
                        'so_cho' => fake()->randomElement([40, 45, 50, 55, 60]),
                        'nam_san_xuat' => $namSX,
                        'ngay_bat_dau_van_hanh' => fake()->dateTimeBetween(
                            "{$namSX}-01-01",
                            "{$namSX}-12-31"
                        ),
                        'ghi_chu' => "Xe buýt phục vụ tuyến {$tuyenXe->ma_tuyen} — {$tuyenXe->ten_tuyen}",
                    ]
                );
            }
        }
    }

    private function taoBienSo(string $maTuyen, int $soThu): string
    {
        $prefixes = ['43A', '43B', '43C', '43D', '43K'];
        $prefix = $prefixes[abs(crc32($maTuyen)) % count($prefixes)];
        $numbers = fake()->numerify('####');
        $suffixes = fake()->randomElement(['', '-####']);

        return sprintf('%s-%s%s', $prefix, $numbers, $suffixes);
    }
}
