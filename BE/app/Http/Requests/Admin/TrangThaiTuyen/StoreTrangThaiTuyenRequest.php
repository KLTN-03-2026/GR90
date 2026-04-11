<?php

namespace App\Http\Requests\Admin\TrangThaiTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;

class StoreTrangThaiTuyenRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'ma_trang_thai' => 'required|string|max:30|unique:trang_thai_tuyens,ma_trang_thai',
            'ten_trang_thai' => 'required|string|max:150',
        ];
    }
}
