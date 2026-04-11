<?php

namespace App\Http\Requests\Admin\LoTrinhTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteLoTrinhTuyenRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['lo_trinh_tuyen_id' => $this->route('lo_trinh_tuyen')]);
    }

    public function rules(): array
    {
        return [
            'lo_trinh_tuyen_id' => 'required|integer|exists:lo_trinh_tuyens,id',
        ];
    }
}
