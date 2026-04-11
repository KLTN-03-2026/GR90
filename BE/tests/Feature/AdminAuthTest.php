<?php

use App\Models\QuanTriVien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function taoQuanTriVien(array $overrides = []): QuanTriVien
{
    return QuanTriVien::query()->create(array_merge([
        'ma_quan_tri_vien' => 'ADMIN001',
        'ten_dang_nhap' => 'admin',
        'password' => '123456',
        'ho_ten' => 'Super Admin',
        'email' => 'admin@example.com',
        'trang_thai' => 1,
    ], $overrides));
}

it('chặn truy cập api admin khi chưa đăng nhập', function () {
    $this->getJson('/api/admin/tai-khoan/quan-tri-viens')
        ->assertStatus(401)
        ->assertJsonPath('message', 'Bạn cần đăng nhập để tiếp tục.');
});

it('đăng nhập admin bằng sanctum và lấy được thông tin phiên', function () {
    $quanTriVien = taoQuanTriVien();

    $response = $this->postJson('/api/admin/auth/login', [
        'login' => $quanTriVien->ten_dang_nhap,
        'password' => '123456',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.admin.email', $quanTriVien->email);

    $token = $response->json('data.access_token');

    expect($token)->not->toBeEmpty();

    $this->withToken($token)
        ->getJson('/api/admin/auth/me')
        ->assertOk()
        ->assertJsonPath('data.email', $quanTriVien->email);
});

it('từ chối token không phải của quản trị viên', function () {
    $user = User::factory()->create();
    $token = $user->createToken('user-panel')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/admin/auth/me')
        ->assertStatus(403)
        ->assertJsonPath('message', 'Bạn không có quyền truy cập khu vực quản trị.');
});