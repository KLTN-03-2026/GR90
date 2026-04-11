<?php

namespace App\Http\Requests\Admin\Auth;

use App\Http\Requests\Admin\BaseAdminRequest;

class UpdateAdminProfileRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'ho_ten' => 'required|string|max:150',
            'so_dien_thoai' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'ho_ten.required' => 'Họ tên là bắt buộc.',
            'ho_ten.max' => 'Họ tên không được vượt quá 150 ký tự.',
            'so_dien_thoai.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
        ]);
    }

    public function attributes(): array
    {
        return [
            'ho_ten' => 'Họ tên',
            'so_dien_thoai' => 'Số điện thoại',
        ];
    }
}