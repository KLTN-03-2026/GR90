<?php

namespace App\Http\Requests\Admin\ChiTietLoTrinh;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteChiTietLoTrinhRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['chi_tiet_lo_trinh_id' => $this->route('chi_tiet_lo_trinh')]);
    }

    public function rules(): array
    {
        return [
            'chi_tiet_lo_trinh_id' => 'required|integer|exists:chi_tiet_lo_trinhs,id',
        ];
    }
}
