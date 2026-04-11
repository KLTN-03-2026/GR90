<?php

namespace App\Http\Requests\Admin\GiaVeTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;

class UpdateGiaVeTuyenRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'tuyen_xe_id' => 'sometimes|required|integer|exists:tuyen_xes,id',
            'loai_gia_ve' => 'sometimes|required|string|max:100',
            'so_tien' => 'sometimes|required|numeric',
            'don_vi_tien_te' => 'nullable|string|max:10',
            'ghi_chu' => 'nullable|string|max:255',
        ];
    }
}
