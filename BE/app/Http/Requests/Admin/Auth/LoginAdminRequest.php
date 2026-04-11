<?php

namespace App\Http\Requests\Admin\Auth;

use App\Http\Requests\Admin\BaseAdminRequest;

class LoginAdminRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'login' => 'required|string|max:150',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'login.required' => 'Email hoặc tên đăng nhập là bắt buộc.',
            'login.string' => 'Email hoặc tên đăng nhập phải là chuỗi.',
            'login.max' => 'Email hoặc tên đăng nhập không được vượt quá 150 ký tự.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.string' => 'Mật khẩu phải là chuỗi.',
        ]);
    }

    public function attributes(): array
    {
        return [
            'login' => 'Email hoặc tên đăng nhập',
            'password' => 'Mật khẩu',
        ];
    }
}