<?php

namespace App\Http\Requests\Admin\TuyenXe;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteTuyenXeRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['tuyen_xe_id' => $this->route('tuyen_xe')]);
    }

    public function rules(): array
    {
        return [
            'tuyen_xe_id' => 'required|integer|exists:tuyen_xes,id',
        ];
    }
}
