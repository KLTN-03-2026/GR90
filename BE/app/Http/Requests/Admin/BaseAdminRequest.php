<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseAdminRequest extends FormRequest
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
            'integer' => ':attribute phải là số nguyên.',
            'numeric' => ':attribute phải là số.',
            'date' => ':attribute phải đúng định dạng ngày.',
            'date_format' => ':attribute phải đúng định dạng giờ.',
            'in' => ':attribute không hợp lệ.',
            'exists' => ':attribute không tồn tại trong hệ thống.',
            'unique' => ':attribute đã tồn tại.',
            'nullable' => ':attribute không hợp lệ.',
            'url' => ':attribute phải là đường dẫn hợp lệ.',
            'between' => ':attribute không nằm trong khoảng cho phép.',
            'boolean' => ':attribute phải là true/false.',
            'array' => ':attribute phải là một danh sách hợp lệ.',
            'confirmed' => ':attribute xác nhận không khớp.',
        ];
    }
}