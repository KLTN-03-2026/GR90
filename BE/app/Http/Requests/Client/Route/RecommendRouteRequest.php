<?php

namespace App\Http\Requests\Client\Route;

use App\Http\Requests\Client\BaseClientRequest;

class RecommendRouteRequest extends BaseClientRequest
{
    public function rules(): array
    {
        return [
            'diem_di' => ['nullable', 'string', 'max:255'],
            'diem_den' => ['nullable', 'string', 'max:255'],
            'tu_khoa' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }
}
