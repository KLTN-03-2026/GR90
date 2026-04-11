<?php

namespace App\Http\Requests\Admin\QuanTriVien;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class UpdateQuanTriVienRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('quan_tri_vien');

        return [
            'ma_quan_tri_vien' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('quan_tri_viens', 'ma_quan_tri_vien')->ignore($id)],
            'ten_dang_nhap' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('quan_tri_viens', 'ten_dang_nhap')->ignore($id)],
            'password' => 'nullable|string|min:6',
            'ho_ten' => 'sometimes|required|string|max:150',
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('quan_tri_viens', 'email')->ignore($id)],
            'so_dien_thoai' => 'nullable|string|max:20',
            'trang_thai' => 'nullable|integer|in:0,1',
            'is_master' => 'nullable|integer|in:0,1',
            'quyen_ids' => 'nullable|array',
            'quyen_ids.*' => 'integer|exists:phan_quyen_quan_tri_viens,id',
            'lan_dang_nhap_cuoi' => 'nullable|date',
        ];
    }
}