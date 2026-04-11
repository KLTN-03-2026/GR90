<?php

namespace App\Http\Requests\Admin\PhanQuyenQuanTriVien;

use App\Http\Requests\Admin\BaseAdminRequest;

class StorePhanQuyenQuanTriVienRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'ten_quyen' => 'required|string|max:100|unique:phan_quyen_quan_tri_viens,ten_quyen',
            'mo_ta' => 'nullable|string|max:255',
            'chuc_nang_ids' => 'nullable|array',
            'chuc_nang_ids.*' => 'integer|exists:chuc_nangs,id',
        ];
    }
}
