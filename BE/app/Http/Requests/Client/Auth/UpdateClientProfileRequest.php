<?php

namespace App\Http\Requests\Client\Auth;

use App\Http\Requests\Client\BaseClientRequest;

class UpdateClientProfileRequest extends BaseClientRequest
{
    public function rules(): array
    {
        return [
            'ho_ten' => ['required', 'string', 'max:150'],
            'so_dien_thoai' => ['nullable', 'string', 'max:20'],
        ];
    }
}
