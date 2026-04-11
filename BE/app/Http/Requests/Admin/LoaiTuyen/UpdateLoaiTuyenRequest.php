<?php

namespace App\Http\Requests\Admin\LoaiTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class UpdateLoaiTuyenRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('loai_tuyen');

        return [
            'ma_loai_tuyen' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('loai_tuyens', 'ma_loai_tuyen')->ignore($id)],
            'ten_loai_tuyen' => 'sometimes|required|string|max:150',
            'mo_ta' => 'nullable|string|max:255',
        ];
    }
}
