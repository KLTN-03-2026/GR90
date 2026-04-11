<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TinNhanChatbot extends Model
{
    use HasFactory;

    protected $table = 'tin_nhan_chatbots';

    const UPDATED_AT = null;

    protected $fillable = [
        'hoi_thoai_chatbot_id',
        'vai_tro',
        'noi_dung',
        'y_dinh_du_doan',
        'do_tin_cay',
        'thoi_diem_gui',
    ];

    protected $casts = [
        'do_tin_cay' => 'decimal:2',
        'thoi_diem_gui' => 'datetime',
    ];

    public function hoiThoaiChatbot(): BelongsTo
    {
        return $this->belongsTo(HoiThoaiChatbot::class, 'hoi_thoai_chatbot_id');
    }
}
