<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoTrinhTuyen extends Model
{
    use HasFactory;

    protected $table = 'lo_trinh_tuyens';

    protected $fillable = [
        'tuyen_xe_id',
        'chieu',
        'mo_ta_lo_trinh',
    ];

    public function tuyenXe(): BelongsTo
    {
        return $this->belongsTo(TuyenXe::class, 'tuyen_xe_id');
    }

    public function chiTietLoTrinhs(): HasMany
    {
        return $this->hasMany(ChiTietLoTrinh::class, 'lo_trinh_tuyen_id');
    }
}
