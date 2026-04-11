<?php

namespace App\Models;

use App\Models\Concerns\HasMaCongKhai;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class KhachHang extends Authenticatable
{
    use HasApiTokens, HasFactory, HasMaCongKhai, Notifiable;

    protected $table = 'khach_hangs';

    protected $fillable = [
        'ma_cong_khai',
        'ma_khach_hang',
        'ten_dang_nhap',
        'password',
        'ho_ten',
        'email',
        'so_dien_thoai',
        'anh_dai_dien',
        'trang_thai',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'trang_thai' => 'integer',
    ];

    public function tuyenYeuThichs(): HasMany
    {
        return $this->hasMany(TuyenYeuThich::class, 'khach_hang_id');
    }

    public function lichSuTraCuus(): HasMany
    {
        return $this->hasMany(LichSuTraCuu::class, 'khach_hang_id');
    }

    public function luotXemTuyens(): HasMany
    {
        return $this->hasMany(LuotXemTuyen::class, 'khach_hang_id');
    }

    public function hoiThoaiChatbots(): HasMany
    {
        return $this->hasMany(HoiThoaiChatbot::class, 'khach_hang_id');
    }
}
