<?php

namespace App\Http\Requests\Admin\KhachHang;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class UpdateKhachHangRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('khach_hang');

        return [
            'ma_khach_hang' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('khach_hangs', 'ma_khach_hang')->ignore($id)],
            'ten_dang_nhap' => ['nullable', 'string', 'max:100', Rule::unique('khach_hangs', 'ten_dang_nhap')->ignore($id)],
            'password' => 'nullable|string|min:6',
            'ho_ten' => 'sometimes|required|string|max:150',
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('khach_hangs', 'email')->ignore($id)],
            'so_dien_thoai' => 'nullable|string|max:20',
            'anh_dai_dien' => 'nullable|string|max:255|url',
            'trang_thai' => 'nullable|integer|in:0,1',
        ];
    }
}
