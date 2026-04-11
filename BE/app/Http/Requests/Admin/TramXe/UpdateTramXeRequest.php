<?php

namespace App\Http\Requests\Admin\TramXe;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class UpdateTramXeRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('tram_xe');

        return [
            'ma_tram' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('tram_xes', 'ma_tram')->ignore($id)],
            'ten_tram' => 'sometimes|required|string|max:255',
            'dia_chi' => 'nullable|string|max:255',
            'vi_do' => 'nullable|numeric|between:-90,90',
            'kinh_do' => 'nullable|numeric|between:-180,180',
            'khu_vuc' => 'nullable|string|max:150',
        ];
    }
}
