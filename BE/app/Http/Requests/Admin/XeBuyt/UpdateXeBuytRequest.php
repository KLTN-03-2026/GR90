<?php

namespace App\Http\Requests\Admin\XeBuyt;

use App\Http\Requests\Admin\BaseAdminRequest;

class UpdateXeBuytRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'bien_so' => 'sometimes|string|max:20|unique:xe_buyts,bien_so,' . $this->route('id'),
            'ten_xe' => 'sometimes|string|max:255',
            'tuyen_xe_id' => 'sometimes|integer|exists:tuyen_xes,id',
            'trang_thai' => 'nullable|string|max:50',
            'loai_xe' => 'nullable|string|max:100',
            'so_cho' => 'nullable|integer|min:1|max:200',
            'nam_san_xuat' => 'nullable|integer|min:1900|max:2099',
            'ngay_bat_dau_van_hanh' => 'nullable|date',
            'ghi_chu' => 'nullable|string',
        ];
    }
}
