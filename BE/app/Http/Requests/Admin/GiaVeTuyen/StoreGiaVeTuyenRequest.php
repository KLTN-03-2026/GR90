<?php

namespace App\Http\Requests\Admin\GiaVeTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;

class StoreGiaVeTuyenRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'tuyen_xe_id' => 'required|integer|exists:tuyen_xes,id',
            'loai_gia_ve' => 'required|string|max:100',
            'so_tien' => 'required|numeric',
            'don_vi_tien_te' => 'nullable|string|max:10',
            'ghi_chu' => 'nullable|string|max:255',
        ];
    }
}
