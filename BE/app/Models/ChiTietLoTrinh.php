<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietLoTrinh extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_lo_trinhs';

    protected $fillable = [
        'lo_trinh_tuyen_id',
        'tram_xe_id',
        'thu_tu_dung',
        'ten_diem_di_qua',
        'thoi_gian_dung_du_kien_giay',
        'khoang_cach_tu_diem_truoc_met',
    ];

    protected $casts = [
        'thu_tu_dung' => 'integer',
        'thoi_gian_dung_du_kien_giay' => 'integer',
        'khoang_cach_tu_diem_truoc_met' => 'integer',
    ];

    public function loTrinhTuyen(): BelongsTo
    {
        return $this->belongsTo(LoTrinhTuyen::class, 'lo_trinh_tuyen_id');
    }

    public function tramXe(): BelongsTo
    {
        return $this->belongsTo(TramXe::class, 'tram_xe_id');
    }
}
