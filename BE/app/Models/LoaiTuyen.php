<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoaiTuyen extends Model
{
    use HasFactory;

    protected $table = 'loai_tuyens';

    protected $fillable = [
        'ma_loai_tuyen',
        'ten_loai_tuyen',
        'mo_ta',
    ];

    public function tuyenXes(): HasMany
    {
        return $this->hasMany(TuyenXe::class, 'loai_tuyen_id');
    }
}
