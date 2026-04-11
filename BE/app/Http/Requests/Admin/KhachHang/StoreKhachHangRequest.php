<?php

namespace App\Http\Requests\Admin\KhachHang;

use App\Http\Requests\Admin\BaseAdminRequest;

class StoreKhachHangRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'ma_khach_hang' => 'required|string|max:30|unique:khach_hangs,ma_khach_hang',
            'ten_dang_nhap' => 'nullable|string|max:100|unique:khach_hangs,ten_dang_nhap',
            'password' => 'nullable|string|min:6',
            'ho_ten' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:khach_hangs,email',
            'so_dien_thoai' => 'nullable|string|max:20',
            'anh_dai_dien' => 'nullable|string|max:255|url',
            'trang_thai' => 'nullable|integer|in:0,1',
        ];
    }
}
