<?php

namespace App\Http\Requests\Admin\TrangThaiTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteTrangThaiTuyenRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['trang_thai_tuyen_id' => $this->route('trang_thai_tuyen')]);
    }

    public function rules(): array
    {
        return [
            'trang_thai_tuyen_id' => 'required|integer|exists:trang_thai_tuyens,id',
        ];
    }
}
