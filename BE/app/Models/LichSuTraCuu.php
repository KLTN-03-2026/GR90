<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichSuTraCuu extends Model
{
    use HasFactory;

    protected $table = 'lich_su_tra_cuus';

    protected $fillable = [
        'khach_hang_id',
        'diem_di',
        'diem_den',
        'tu_khoa_tim_kiem',
        'ket_qua_goi_y_json',
        'thoi_gian_tra_cuu',
    ];

    protected $casts = [
        'ket_qua_goi_y_json' => 'array',
        'thoi_gian_tra_cuu' => 'datetime',
    ];

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }
}
