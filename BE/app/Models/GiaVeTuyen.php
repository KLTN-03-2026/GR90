<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiaVeTuyen extends Model
{
    use HasFactory;

    protected $table = 'gia_ve_tuyens';

    protected $fillable = [
        'tuyen_xe_id',
        'loai_gia_ve',
        'so_tien',
        'don_vi_tien_te',
        'ghi_chu',
    ];

    protected $casts = [
        'so_tien' => 'decimal:2',
    ];

    public function tuyenXe(): BelongsTo
    {
        return $this->belongsTo(TuyenXe::class, 'tuyen_xe_id');
    }
}
