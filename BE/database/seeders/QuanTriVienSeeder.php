<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\QuanTriVien;

class QuanTriVienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        QuanTriVien::query()->updateOrCreate(
            [ 'email' => 'admin@localhost' ],
            [
                'ho_ten'            => 'Super Admin',
                'ten_dang_nhap'     => 'admin',
                'ma_quan_tri_vien'  => 'ADMIN001',
                'ma_cong_khai'      => 'ADMIN001',
                'email'             => 'admin@localhost',
                'password'          => Hash::make('123456'),
                'trang_thai'        => 1,
                'is_master'         => 1,
            ]
        );
    }
}
