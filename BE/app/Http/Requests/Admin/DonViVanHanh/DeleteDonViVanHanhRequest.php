<?php

namespace App\Http\Requests\Admin\DonViVanHanh;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteDonViVanHanhRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['don_vi_van_hanh_id' => $this->route('don_vi_van_hanh')]);
    }

    public function rules(): array
    {
        return [
            'don_vi_van_hanh_id' => 'required|integer|exists:don_vi_van_hanhs,id',
        ];
    }
}
