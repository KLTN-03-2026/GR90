<?php

namespace App\Http\Requests\Admin\PhanQuyenQuanTriVien;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeletePhanQuyenQuanTriVienRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['phan_quyen_id' => $this->route('phan_quyen')]);
    }

    public function rules(): array
    {
        return [
            'phan_quyen_id' => 'required|integer|exists:phan_quyen_quan_tri_viens,id',
        ];
    }
}
