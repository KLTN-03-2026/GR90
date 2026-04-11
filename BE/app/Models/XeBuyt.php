<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XeBuyt extends Model
{
    use HasFactory;

    protected $table = 'xe_buyts';

    protected $fillable = [
        'bien_so',
        'ten_xe',
        'tuyen_xe_id',
        'trang_thai',
        'loai_xe',
        'so_cho',
        'nam_san_xuat',
        'ngay_bat_dau_van_hanh',
        'ghi_chu',
    ];

    protected $casts = [
        'so_cho' => 'integer',
        'nam_san_xuat' => 'integer',
        'ngay_bat_dau_van_hanh' => 'date',
    ];

    public function tuyenXe(): BelongsTo
    {
        return $this->belongsTo(TuyenXe::class, 'tuyen_xe_id');
    }
}
