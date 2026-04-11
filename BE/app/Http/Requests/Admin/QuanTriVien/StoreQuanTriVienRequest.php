<?php

namespace App\Http\Requests\Admin\QuanTriVien;

use App\Http\Requests\Admin\BaseAdminRequest;

class StoreQuanTriVienRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'ma_quan_tri_vien' => 'required|string|max:30|unique:quan_tri_viens,ma_quan_tri_vien',
            'ten_dang_nhap' => 'required|string|max:100|unique:quan_tri_viens,ten_dang_nhap',
            'password' => 'required|string|min:6',
            'ho_ten' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:quan_tri_viens,email',
            'so_dien_thoai' => 'nullable|string|max:20',
            'trang_thai' => 'nullable|integer|in:0,1',
            'is_master' => 'nullable|integer|in:0,1',
            'quyen_ids' => 'nullable|array',
            'quyen_ids.*' => 'integer|exists:phan_quyen_quan_tri_viens,id',
            'lan_dang_nhap_cuoi' => 'nullable|date',
        ];
    }
}