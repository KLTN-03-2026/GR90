<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrangThaiTuyen extends Model
{
    use HasFactory;

    protected $table = 'trang_thai_tuyens';

    protected $fillable = [
        'ma_trang_thai',
        'ten_trang_thai',
    ];

    public function tuyenXes(): HasMany
    {
        return $this->hasMany(TuyenXe::class, 'trang_thai_tuyen_id');
    }
}
