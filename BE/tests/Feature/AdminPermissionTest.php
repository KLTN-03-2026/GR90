<?php

use App\Models\ChucNang;
use App\Models\PhanQuyenQuanTriVien;
use App\Models\QuanTriVien;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function taoQuanTriVienPhanQuyen(array $overrides = []): QuanTriVien
{
    static $counter = 1;

    $index = $counter++;

    return QuanTriVien::query()->create(array_merge([
        'ma_quan_tri_vien' => sprintf('ADMIN%03d', $index),
        'ten_dang_nhap' => 'admin_' . $index,
        'password' => '123456',
        'ho_ten' => 'Quản trị viên ' . $index,
        'email' => 'admin' . $index . '@example.com',
        'trang_thai' => 1,
        'is_master' => 0,
    ], $overrides));
}

function ganQuyenRoute(QuanTriVien $quanTriVien, string $routeName, string $permissionName = 'Quyền thử nghiệm'): void
{
    $permission = PhanQuyenQuanTriVien::query()->create([
        'ten_quyen' => $permissionName,
        'mo_ta' => 'Quyền kiểm thử theo route name.',
    ]);

    $function = ChucNang::query()->create([
        'ma_chuc_nang' => strtoupper(str_replace(['.', '-'], '_', $routeName)),
        'ten_chuc_nang' => 'Chức năng ' . $routeName,
        'route_name' => $routeName,
        'uri' => 'api/test',
        'http_method' => 'GET',
        'nhom' => 'Kiểm thử',
        'mo_ta' => 'Dùng cho kiểm thử middleware quyền.',
    ]);

    $permission->chucNangs()->attach($function->id);
    $quanTriVien->quyenQuanTriViens()->attach($permission->id);
}

it('cho phep master truy cap api business du chua gan quyen', function () {
    $quanTriVien = taoQuanTriVienPhanQuyen([
        'is_master' => 1,
    ]);

    $token = $quanTriVien->createToken('master')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/admin/tai-khoan/quan-tri-viens')
        ->assertOk();
});

it('chan admin thuong khi khong co quyen phu hop voi route', function () {
    $quanTriVien = taoQuanTriVienPhanQuyen();
    $token = $quanTriVien->createToken('staff')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/admin/tai-khoan/quan-tri-viens')
        ->assertStatus(403)
        ->assertJsonPath('message', 'Bạn không có quyền thực hiện chức năng này.');
});

it('cho phep admin thuong khi duoc gan quyen theo route name', function () {
    $quanTriVien = taoQuanTriVienPhanQuyen();
    ganQuyenRoute($quanTriVien, 'admin.tai-khoan.quan-tri-viens.index');

    $token = $quanTriVien->createToken('staff')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/admin/tai-khoan/quan-tri-viens')
        ->assertOk();
});

it('khong dung middleware quyen cho cac api ca nhan cua admin', function () {
    $quanTriVien = taoQuanTriVienPhanQuyen();
    $token = $quanTriVien->createToken('staff')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/admin/auth/me')
        ->assertOk()
        ->assertJsonPath('data.email', $quanTriVien->email);
});
