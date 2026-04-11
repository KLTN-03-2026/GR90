<?php

namespace App\Http\Requests\Client\Auth;

use App\Http\Requests\Client\BaseClientRequest;

class UpdateClientPasswordRequest extends BaseClientRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }
}
