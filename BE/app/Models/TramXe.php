<?php

namespace App\Models;

use App\Models\Concerns\HasMaCongKhai;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TramXe extends Model
{
    use HasFactory, HasMaCongKhai;

    protected $table = 'tram_xes';

    protected $fillable = [
        'ma_cong_khai',
        'ma_tram',
        'ten_tram',
        'dia_chi',
        'vi_do',
        'kinh_do',
        'khu_vuc',
    ];

    protected $casts = [
        'vi_do' => 'decimal:7',
        'kinh_do' => 'decimal:7',
    ];

    public function chiTietLoTrinhs(): HasMany
    {
        return $this->hasMany(ChiTietLoTrinh::class, 'tram_xe_id');
    }
}
