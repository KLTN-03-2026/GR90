<?php

namespace Database\Seeders;

use App\Models\PhanQuyenQuanTriVien;
use Illuminate\Database\Seeder;

class PhanQuyenQuanTriVienSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['ten_quyen' => 'Quản lý quyền', 'mo_ta' => 'Quản lý nhóm quyền và gán chức năng cho từng quyền.'],
            ['ten_quyen' => 'Quản lý quản trị viên', 'mo_ta' => 'Quản lý tài khoản quản trị viên và phân quyền.'],
            ['ten_quyen' => 'Quản lý khách hàng', 'mo_ta' => 'Quản lý danh sách khách hàng.'],
            ['ten_quyen' => 'Quản lý loại tuyến', 'mo_ta' => 'Quản lý danh mục loại tuyến.'],
            ['ten_quyen' => 'Quản lý trạng thái tuyến', 'mo_ta' => 'Quản lý danh mục trạng thái tuyến.'],
            ['ten_quyen' => 'Quản lý đơn vị vận hành', 'mo_ta' => 'Quản lý đơn vị vận hành tuyến xe.'],
            ['ten_quyen' => 'Quản lý trạm xe', 'mo_ta' => 'Quản lý danh mục trạm xe.'],
            ['ten_quyen' => 'Quản lý tuyến xe', 'mo_ta' => 'Quản lý dữ liệu tuyến xe.'],
            ['ten_quyen' => 'Quản lý lộ trình tuyến', 'mo_ta' => 'Quản lý lộ trình tuyến xe.'],
            ['ten_quyen' => 'Quản lý giá vé tuyến', 'mo_ta' => 'Quản lý bảng giá vé tuyến.'],
            ['ten_quyen' => 'Quản lý chi tiết lộ trình', 'mo_ta' => 'Quản lý các điểm chi tiết của lộ trình.'],
        ];

        foreach ($permissions as $permission) {
            PhanQuyenQuanTriVien::query()->updateOrCreate(
                ['ten_quyen' => $permission['ten_quyen']],
                ['mo_ta' => $permission['mo_ta']]
            );
        }
    }
}
