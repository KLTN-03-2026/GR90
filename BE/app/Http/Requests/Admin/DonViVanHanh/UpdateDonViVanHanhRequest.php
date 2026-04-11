<?php

namespace App\Http\Requests\Admin\DonViVanHanh;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class UpdateDonViVanHanhRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('don_vi_van_hanh');

        return [
            'ma_don_vi' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('don_vi_van_hanhs', 'ma_don_vi')->ignore($id)],
            'ten_don_vi' => 'sometimes|required|string|max:200',
            'dia_chi' => 'nullable|string|max:255',
            'so_dien_thoai' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
        ];
    }
}
