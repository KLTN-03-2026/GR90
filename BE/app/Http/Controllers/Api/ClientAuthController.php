<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Auth\LoginClientRequest;
use App\Http\Requests\Client\Auth\RegisterClientRequest;
use App\Http\Requests\Client\Auth\UpdateClientPasswordRequest;
use App\Http\Requests\Client\Auth\UpdateClientProfileRequest;
use App\Models\KhachHang;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientAuthController extends Controller
{
    use ApiResponseTrait;

    public function register(RegisterClientRequest $request)
    {
        $payload = $request->validated();

        $khachHang = KhachHang::query()->create([
            'ma_khach_hang' => $this->taoMaKhachHangKhongTrung(),
            'ten_dang_nhap' => $this->taoTenDangNhap($payload['email']),
            'ho_ten' => $payload['ho_ten'],
            'email' => $payload['email'],
            'so_dien_thoai' => $payload['so_dien_thoai'] ?? null,
            'password' => $payload['password'],
            'trang_thai' => 1,
        ]);

        $token = $khachHang->createToken('client-app')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $khachHang->fresh(),
        ], 'Đăng ký tài khoản thành công.');
    }

    public function login(LoginClientRequest $request)
    {
        $payload = $request->validated();
        $login = trim($payload['login']);

        $khachHang = KhachHang::query()
            ->where(function ($query) use ($login) {
                $query->where('email', $login)
                    ->orWhere('ten_dang_nhap', $login);
            })
            ->first();

        if (! $khachHang || ! $khachHang->password || ! Hash::check($payload['password'], $khachHang->password)) {
            throw new ApiRequestException('Thông tin đăng nhập không chính xác.');
        }

        if ((int) $khachHang->trang_thai !== 1) {
            throw new ApiRequestException('Tài khoản khách hàng đã bị khóa.');
        }

        $token = $khachHang->createToken('client-app')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $khachHang->fresh(),
        ], 'Đăng nhập thành công.');
    }

    public function me(Request $request)
    {
        return $this->dataResponse($request->user(), 'Lấy thông tin khách hàng thành công.');
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->successResponse(null, 'Đăng xuất thành công.');
    }

    public function updateProfile(UpdateClientProfileRequest $request)
    {
        /** @var KhachHang $khachHang */
        $khachHang = $request->user();
        $khachHang->update($request->validated());

        return $this->successResponse($khachHang->fresh(), 'Cap nhat thong tin ca nhan thanh cong.');
    }

    public function updatePassword(UpdateClientPasswordRequest $request)
    {
        /** @var KhachHang $khachHang */
        $khachHang = $request->user();
        $payload = $request->validated();

        if (! $khachHang->password || ! Hash::check($payload['current_password'], $khachHang->password)) {
            throw new ApiRequestException('Mat khau hien tai khong chinh xac.');
        }

        $khachHang->update([
            'password' => $payload['password'],
        ]);

        return $this->successResponse(null, 'Doi mat khau thanh cong.');
    }

    private function taoMaKhachHangKhongTrung(): string
    {
        do {
            $ma = 'KH' . now()->format('ymd') . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (KhachHang::query()->where('ma_khach_hang', $ma)->exists());

        return $ma;
    }

    private function taoTenDangNhap(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'), '_');
        $base = $base !== '' ? $base : 'khach_hang';
        $username = $base;
        $counter = 1;

        while (KhachHang::query()->where('ten_dang_nhap', $username)->exists()) {
            $counter += 1;
            $username = "{$base}_{$counter}";
        }

        return $username;
    }
}
