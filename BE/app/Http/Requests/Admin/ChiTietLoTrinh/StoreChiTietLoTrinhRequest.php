<?php

namespace App\Http\Requests\Admin\ChiTietLoTrinh;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class StoreChiTietLoTrinhRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $loTrinhTuyenId = (int) $this->input('lo_trinh_tuyen_id');

        return [
            'lo_trinh_tuyen_id' => 'required|integer|exists:lo_trinh_tuyens,id',
            'tram_xe_id' => 'nullable|integer|exists:tram_xes,id',
            'thu_tu_dung' => [
                'required',
                'integer',
                Rule::unique('chi_tiet_lo_trinhs', 'thu_tu_dung')->where(fn ($query) => $query->where('lo_trinh_tuyen_id', $loTrinhTuyenId)),
            ],
            'ten_diem_di_qua' => 'nullable|string|max:255',
            'thoi_gian_dung_du_kien_giay' => 'nullable|integer',
            'khoang_cach_tu_diem_truoc_met' => 'nullable|integer',
        ];
    }
}
