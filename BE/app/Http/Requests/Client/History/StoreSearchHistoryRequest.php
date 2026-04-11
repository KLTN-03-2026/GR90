<?php

namespace App\Http\Requests\Client\History;

use App\Http\Requests\Client\BaseClientRequest;

class StoreSearchHistoryRequest extends BaseClientRequest
{
    public function rules(): array
    {
        return [
            'diem_di' => ['nullable', 'string', 'max:255'],
            'diem_den' => ['nullable', 'string', 'max:255'],
            'tu_khoa_tim_kiem' => ['nullable', 'string', 'max:255'],
            'ket_qua_goi_y_json' => ['nullable', 'array'],
        ];
    }
}
