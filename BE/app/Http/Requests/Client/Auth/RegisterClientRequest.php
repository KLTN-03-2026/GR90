<?php

namespace App\Http\Requests\Client\Auth;

use App\Http\Requests\Client\BaseClientRequest;
use Illuminate\Validation\Rule;

class RegisterClientRequest extends BaseClientRequest
{
    public function rules(): array
    {
        return [
            'ho_ten' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('khach_hangs', 'email')],
            'so_dien_thoai' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'agree' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ho_ten' => 'Họ và tên',
            'email' => 'Email',
            'so_dien_thoai' => 'Số điện thoại',
            'password' => 'Mật khẩu',
            'agree' => 'Điều khoản sử dụng',
        ];
    }

    public function messages(): array
    {
        return parent::messages();
    }
}
