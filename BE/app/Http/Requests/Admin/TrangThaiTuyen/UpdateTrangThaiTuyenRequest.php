<?php

namespace App\Http\Requests\Admin\TrangThaiTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class UpdateTrangThaiTuyenRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('trang_thai_tuyen');

        return [
            'ma_trang_thai' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('trang_thai_tuyens', 'ma_trang_thai')->ignore($id)],
            'ten_trang_thai' => 'sometimes|required|string|max:150',
        ];
    }
}
