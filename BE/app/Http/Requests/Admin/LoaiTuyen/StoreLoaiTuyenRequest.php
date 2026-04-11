<?php

namespace App\Http\Requests\Admin\LoaiTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;

class StoreLoaiTuyenRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'ma_loai_tuyen' => 'required|string|max:30|unique:loai_tuyens,ma_loai_tuyen',
            'ten_loai_tuyen' => 'required|string|max:150',
            'mo_ta' => 'nullable|string|max:255',
        ];
    }
}
