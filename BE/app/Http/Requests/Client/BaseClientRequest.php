<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute là bắt buộc.',
            'string' => ':attribute phải là chuỗi.',
            'email' => ':attribute không đúng định dạng email.',
            'max' => ':attribute vượt quá độ dài cho phép.',
            'min' => ':attribute không đạt độ dài tối thiểu.',
            'unique' => ':attribute đã tồn tại.',
            'confirmed' => ':attribute xác nhận không khớp.',
            'boolean' => ':attribute không hợp lệ.',
            'accepted' => 'Bạn cần chấp nhận :attribute để tiếp tục.',
        ];
    }
}
