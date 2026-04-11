<?php

namespace App\Http\Requests\Admin\DonViVanHanh;

use App\Http\Requests\Admin\BaseAdminRequest;

class StoreDonViVanHanhRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'ma_don_vi' => 'required|string|max:30|unique:don_vi_van_hanhs,ma_don_vi',
            'ten_don_vi' => 'required|string|max:200',
            'dia_chi' => 'nullable|string|max:255',
            'so_dien_thoai' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
        ];
    }
}
