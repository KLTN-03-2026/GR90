<?php

namespace App\Http\Requests\Admin\TuyenXe;

use App\Http\Requests\Admin\BaseAdminRequest;

class StoreTuyenXeRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'ma_tuyen' => 'required|string|max:30|unique:tuyen_xes,ma_tuyen',
            'ten_tuyen' => 'required|string|max:255',
            'ten_tuyen_cu' => 'nullable|string|max:255',
            'loai_tuyen_id' => 'required|integer|exists:loai_tuyens,id',
            'trang_thai_tuyen_id' => 'required|integer|exists:trang_thai_tuyens,id',
            'don_vi_van_hanh_id' => 'nullable|integer|exists:don_vi_van_hanhs,id',
            'diem_dau' => 'required|string|max:255',
            'diem_cuoi' => 'required|string|max:255',
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
