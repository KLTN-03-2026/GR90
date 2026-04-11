<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HoiThoaiChatbot extends Model
{
    use HasFactory;

    protected $table = 'hoi_thoai_chatbots';

    protected $fillable = [
        'khach_hang_id',
        'ma_phien',
        'kenh',
        'bat_dau_luc',
        'ket_thuc_luc',
    ];

    protected $casts = [
        'bat_dau_luc' => 'datetime',
        'ket_thuc_luc' => 'datetime',
    ];

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    public function tinNhanChatbots(): HasMany
    {
        return $this->hasMany(TinNhanChatbot::class, 'hoi_thoai_chatbot_id');
    }
}
