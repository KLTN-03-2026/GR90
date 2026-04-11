<?php

namespace App\Http\Requests\Admin\LoaiTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteLoaiTuyenRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['loai_tuyen_id' => $this->route('loai_tuyen')]);
    }

    public function rules(): array
    {
        return [
            'loai_tuyen_id' => 'required|integer|exists:loai_tuyens,id',
        ];
    }
}
