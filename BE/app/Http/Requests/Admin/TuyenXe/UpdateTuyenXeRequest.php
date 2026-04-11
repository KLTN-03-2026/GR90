<?php

namespace App\Http\Requests\Admin\TuyenXe;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class UpdateTuyenXeRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('tuyen_xe');

        return [
            'ma_tuyen' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('tuyen_xes', 'ma_tuyen')->ignore($id)],
            'ten_tuyen' => 'sometimes|required|string|max:255',
            'ten_tuyen_cu' => 'nullable|string|max:255',
            'loai_tuyen_id' => 'sometimes|required|integer|exists:loai_tuyens,id',
            'trang_thai_tuyen_id' => 'sometimes|required|integer|exists:trang_thai_tuyens,id',
            'don_vi_van_hanh_id' => 'nullable|integer|exists:don_vi_van_hanhs,id',
            'diem_dau' => 'sometimes|required|string|max:255',
            'diem_cuoi' => 'sometimes|required|string|max:255',
            'thoi_gian_bat_dau_hoat_dong' => 'nullable|date_format:H:i:s',
            'thoi_gian_ket_thuc_hoat_dong' => 'nullable|date_format:H:i:s',
            'tan_suat_phut' => 'nullable|integer',
            'tan_suat_cao_diem_phut' => 'nullable|integer',
            'tan_suat_thap_diem_phut' => 'nullable|integer',
            'cu_ly_km' => 'nullable|numeric',
            'gia_ve_luot' => 'nullable|numeric',
            'ngay_bat_dau_van_hanh' => 'nullable|date',
            'ghi_chu' => 'nullable|string',
            'nguon_du_lieu' => 'nullable|string|max:255',
        ];
    }
}
