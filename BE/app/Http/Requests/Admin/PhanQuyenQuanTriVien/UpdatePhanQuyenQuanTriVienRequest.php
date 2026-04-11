<?php

namespace App\Http\Requests\Admin\PhanQuyenQuanTriVien;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class UpdatePhanQuyenQuanTriVienRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('phan_quyen');

        return [
            'ten_quyen' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('phan_quyen_quan_tri_viens', 'ten_quyen')->ignore($id)],
            'mo_ta' => 'nullable|string|max:255',
            'chuc_nang_ids' => 'nullable|array',
            'chuc_nang_ids.*' => 'integer|exists:chuc_nangs,id',
        ];
    }
}
