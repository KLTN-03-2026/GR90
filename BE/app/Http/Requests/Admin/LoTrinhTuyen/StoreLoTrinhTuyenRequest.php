<?php

namespace App\Http\Requests\Admin\LoTrinhTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

class StoreLoTrinhTuyenRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $tuyenXeId = (int) $this->input('tuyen_xe_id');

        return [
            'tuyen_xe_id' => 'required|integer|exists:tuyen_xes,id',
            'chieu' => [
                'required',
                'in:di,ve',
                Rule::unique('lo_trinh_tuyens', 'chieu')->where(fn ($query) => $query->where('tuyen_xe_id', $tuyenXeId)),
            ],
            'mo_ta_lo_trinh' => 'required|string',
        ];
    }
}
