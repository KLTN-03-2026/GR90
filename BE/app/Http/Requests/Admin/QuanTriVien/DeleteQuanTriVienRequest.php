<?php

namespace App\Http\Requests\Admin\QuanTriVien;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteQuanTriVienRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['quan_tri_vien_id' => $this->route('quan_tri_vien')]);
    }

    public function rules(): array
    {
        return [
            'quan_tri_vien_id' => 'required|integer|exists:quan_tri_viens,id',
        ];
    }
}
