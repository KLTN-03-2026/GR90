<?php

namespace Database\Seeders;

use App\Models\ChucNang;
use App\Models\PhanQuyenQuanTriVien;
use Illuminate\Database\Seeder;

class ChiTietPhanQuyenSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            'Quản lý quyền' => ['admin.tai-khoan.phan-quyens.'],
            'Quản lý quản trị viên' => ['admin.tai-khoan.quan-tri-viens.'],
            'Quản lý khách hàng' => ['admin.tai-khoan.khach-hangs.'],
            'Quản lý loại tuyến' => ['admin.danh-muc.loai-tuyens.'],
            'Quản lý trạng thái tuyến' => ['admin.danh-muc.trang-thai-tuyens.'],
            'Quản lý đơn vị vận hành' => ['admin.danh-muc.don-vi-van-hanhs.'],
            'Quản lý trạm xe' => ['admin.danh-muc.tram-xes.'],
            'Quản lý tuyến xe' => ['admin.van-hanh.tuyen-xes.'],
            'Quản lý lộ trình tuyến' => ['admin.van-hanh.lo-trinh-tuyens.'],
            'Quản lý giá vé tuyến' => ['admin.van-hanh.gia-ve-tuyens.'],
            'Quản lý chi tiết lộ trình' => ['admin.van-hanh.chi-tiet-lo-trinhs.'],
        ];

        foreach ($mapping as $permissionName => $prefixes) {
            $permission = PhanQuyenQuanTriVien::query()->where('ten_quyen', $permissionName)->first();
            if (! $permission) {
                continue;
            }

            $functionIds = ChucNang::query()
                ->where(function ($query) use ($prefixes) {
                    foreach ($prefixes as $prefix) {
                        $query->orWhere('route_name', 'like', $prefix . '%');
                    }
                })
                ->pluck('id')
                ->all();

            $permission->chucNangs()->sync($functionIds);
        }
    }
}
