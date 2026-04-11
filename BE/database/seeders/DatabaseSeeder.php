<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LoaiTuyenSeeder::class,
            TrangThaiTuyenSeeder::class,
            DonViVanHanhSeeder::class,
            TramXeSeeder::class,
            TuyenXeSeeder::class,
            LoTrinhTuyenSeeder::class,
            ChiTietLoTrinhSeeder::class,
            XeBuytSeeder::class,
            ChucNangSeeder::class,
            PhanQuyenQuanTriVienSeeder::class,
            ChiTietPhanQuyenSeeder::class,
            QuanTriVienSeeder::class,
        ]);
    }
}