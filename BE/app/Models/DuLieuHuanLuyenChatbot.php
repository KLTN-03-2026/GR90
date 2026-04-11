<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuLieuHuanLuyenChatbot extends Model
{
    use HasFactory;

    protected $table = 'du_lieu_huan_luyen_chatbots';

    protected $fillable = [
        'cau_hoi',
        'y_dinh',
        'thuc_the_json',
        'cau_tra_loi_mau',
        'nguon',
        'da_duyet',
        'nguoi_duyet_id',
    ];

    protected $casts = [
        'thuc_the_json' => 'array',
        'da_duyet' => 'integer',
    ];

    public function nguoiDuyet(): BelongsTo
    {
        return $this->belongsTo(QuanTriVien::class, 'nguoi_duyet_id');
    }
}
