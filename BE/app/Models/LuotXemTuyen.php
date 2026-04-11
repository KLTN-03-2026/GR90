<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LuotXemTuyen extends Model
{
    use HasFactory;

    protected $table = 'luot_xem_tuyens';

    const UPDATED_AT = null;

    protected $fillable = [
        'khach_hang_id',
        'tuyen_xe_id',
        'dia_chi_ip',
        'thiet_bi',
    ];

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    public function tuyenXe(): BelongsTo
    {
        return $this->belongsTo(TuyenXe::class, 'tuyen_xe_id');
    }
}
