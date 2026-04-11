<?php

namespace App\Http\Requests\Admin\TramXe;

use App\Http\Requests\Admin\BaseAdminRequest;

class StoreTramXeRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'ma_tram' => 'required|string|max:30|unique:tram_xes,ma_tram',
            'ten_tram' => 'required|string|max:255',
            'dia_chi' => 'nullable|string|max:255',
            'vi_do' => 'nullable|numeric|between:-90,90',
            'kinh_do' => 'nullable|numeric|between:-180,180',
            'khu_vuc' => 'nullable|string|max:150',
        ];
    }
}
