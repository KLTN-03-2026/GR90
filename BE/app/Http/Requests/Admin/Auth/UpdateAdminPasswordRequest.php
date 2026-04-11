<?php

namespace App\Http\Requests\Admin\Auth;

use App\Http\Requests\Admin\BaseAdminRequest;

class UpdateAdminPasswordRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'current_password.required' => 'Mật khẩu hiện tại là bắt buộc.',
            'password.required' => 'Mật khẩu mới là bắt buộc.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'Mật khẩu hiện tại',
            'password' => 'Mật khẩu mới',
        ];
    }
}