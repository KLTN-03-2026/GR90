<?php

namespace App\Http\Requests\Admin\TramXe;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteTramXeRequest extends BaseAdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['tram_xe_id' => $this->route('tram_xe')]);
    }

    public function rules(): array
    {
        return [
            'tram_xe_id' => 'required|integer|exists:tram_xes,id',
        ];
    }
}
