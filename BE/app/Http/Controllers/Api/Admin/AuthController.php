<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\ApiRequestException;
use App\Http\Requests\Admin\Auth\LoginAdminRequest;
use App\Http\Requests\Admin\Auth\UpdateAdminPasswordRequest;
use App\Http\Requests\Admin\Auth\UpdateAdminProfileRequest;
use App\Models\QuanTriVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseAdminController
{
    public function login(LoginAdminRequest $request)
    {
        return $this->handle(function () use ($request) {
            $credentials = $request->validated();
            $login = trim($credentials['login']);

            $quanTriVien = QuanTriVien::query()
                ->where(function ($query) use ($login) {
                    $query->where('email', $login)
                        ->orWhere('ten_dang_nhap', $login);
                })
                ->first();

            if (! $quanTriVien || ! Hash::check($credentials['password'], $quanTriVien->password)) {
                throw new ApiRequestException('Thông tin đăng nhập không chính xác.');
            }

            if ((int) $quanTriVien->trang_thai !== 1) {
                throw new ApiRequestException('Tài khoản quản trị viên đã bị khóa.');
            }

            $quanTriVien->forceFill([
                'lan_dang_nhap_cuoi' => now(),
            ])->save();

            $token = $quanTriVien->createToken('admin-panel')->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'admin' => $quanTriVien->fresh(),
            ], 'Đăng nhập thành công.');
        });
    }

    public function me(Request $request)
    {
        return $this->handle(fn () => $this->dataResponse($request->user(), 'Lấy thông tin quản trị viên thành công.'));
    }

    public function logout(Request $request)
    {
        return $this->handle(function () use ($request) {
            $request->user()?->currentAccessToken()?->delete();

            return $this->successResponse(null, 'Đăng xuất thành công.');
        });
    }

    public function updateProfile(UpdateAdminProfileRequest $request)
    {
        return $this->handle(function () use ($request) {
            /** @var QuanTriVien $quanTriVien */
            $quanTriVien = $request->user();

            $quanTriVien->update($request->validated());

            return $this->successResponse($quanTriVien->fresh(), 'Cập nhật thông tin cá nhân thành công.');
        });
    }

    public function updatePassword(UpdateAdminPasswordRequest $request)
    {
        return $this->handle(function () use ($request) {
            /** @var QuanTriVien $quanTriVien */
            $quanTriVien = $request->user();
            $payload = $request->validated();

            if (! Hash::check($payload['current_password'], $quanTriVien->password)) {
                throw new ApiRequestException('Mật khẩu hiện tại không chính xác.');
            }

            $quanTriVien->update([
                'password' => $payload['password'],
            ]);

            return $this->successResponse(null, 'Đổi mật khẩu thành công.');
        });
    }
}