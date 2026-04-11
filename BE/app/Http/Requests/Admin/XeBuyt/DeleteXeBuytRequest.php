<?php

namespace App\Http\Requests\Admin\XeBuyt;

use App\Http\Requests\Admin\BaseAdminRequest;

class DeleteXeBuytRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:xe_buyts,id',
        ];
    }
}
