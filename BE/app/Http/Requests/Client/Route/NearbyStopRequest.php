<?php

namespace App\Http\Requests\Client\Route;

use App\Http\Requests\Client\BaseClientRequest;

class NearbyStopRequest extends BaseClientRequest
{
    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'min:50', 'max:5000'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:30'],
        ];
    }
}
