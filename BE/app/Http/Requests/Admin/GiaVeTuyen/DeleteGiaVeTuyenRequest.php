<?php

namespace App\Http\Requests\Admin\GiaVeTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteGiaVeTuyenRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['gia_ve_tuyen_id' => $this->route('gia_ve_tuyen')]);
    }

    public function rules(): array
    {
        return [
            'gia_ve_tuyen_id' => 'required|integer|exists:gia_ve_tuyens,id',
        ];
    }
}
