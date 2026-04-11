<?php

namespace App\Models;

use App\Models\Concerns\HasMaCongKhai;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonViVanHanh extends Model
{
    use HasFactory, HasMaCongKhai;

    protected $table = 'don_vi_van_hanhs';

    protected $fillable = [
        'ma_cong_khai',
        'ma_don_vi',
        'ten_don_vi',
        'dia_chi',
        'so_dien_thoai',
        'email',
    ];

    public function tuyenXes(): HasMany
    {
        return $this->hasMany(TuyenXe::class, 'don_vi_van_hanh_id');
    }
}
