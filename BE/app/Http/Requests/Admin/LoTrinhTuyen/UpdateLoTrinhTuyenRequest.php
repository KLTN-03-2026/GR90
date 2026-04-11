<?php

namespace App\Http\Requests\Admin\LoTrinhTuyen;

use App\Http\Requests\Admin\BaseAdminRequest;
use App\Models\LoTrinhTuyen;
use Illuminate\Validation\Rule;

class UpdateLoTrinhTuyenRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('lo_trinh_tuyen');
        $tuyenXeId = (int) ($this->input('tuyen_xe_id') ?: LoTrinhTuyen::query()->whereKey($id)->value('tuyen_xe_id'));

        return [
            'tuyen_xe_id' => 'sometimes|required|integer|exists:tuyen_xes,id',
            'chieu' => [
                'sometimes',
                'required',
                'in:di,ve',
                Rule::unique('lo_trinh_tuyens', 'chieu')
                    ->where(fn ($query) => $query->where('tuyen_xe_id', $tuyenXeId))
                    ->ignore($id),
            ],
            'mo_ta_lo_trinh' => 'sometimes|required|string',
        ];
    }
}
