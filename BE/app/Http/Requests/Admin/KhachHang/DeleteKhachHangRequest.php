<?php

namespace App\Http\Requests\Admin\KhachHang;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteKhachHangRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['khach_hang_id' => $this->route('khach_hang')]);
    }

    public function rules(): array
    {
        return [
            'khach_hang_id' => 'required|integer|exists:khach_hangs,id',
        ];
    }
}
