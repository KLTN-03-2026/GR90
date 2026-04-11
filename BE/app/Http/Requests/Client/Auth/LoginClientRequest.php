<?php

namespace App\Http\Requests\Client\Auth;

use App\Http\Requests\Client\BaseClientRequest;

class LoginClientRequest extends BaseClientRequest
{
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'login' => 'Email hoặc tên đăng nhập',
            'password' => 'Mật khẩu',
        ];
    }
}
