<?php

namespace App\Models;

use App\Models\Concerns\HasMaCongKhai;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class QuanTriVien extends Authenticatable
{
    use HasApiTokens, HasFactory, HasMaCongKhai, Notifiable;

    protected $table = 'quan_tri_viens';

    protected $fillable = [
        'ma_cong_khai',
        'ma_quan_tri_vien',
        'ten_dang_nhap',
        'password',
        'ho_ten',
        'email',
        'so_dien_thoai',
        'trang_thai',
        'is_master',
        'lan_dang_nhap_cuoi',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'trang_thai' => 'integer',
        'is_master' => 'integer',
        'lan_dang_nhap_cuoi' => 'datetime',
    ];

    public function quyenQuanTriViens(): BelongsToMany
    {
        return $this->belongsToMany(
            PhanQuyenQuanTriVien::class,
            'quan_tri_vien_quyens',
            'quan_tri_vien_id',
            'phan_quyen_quan_tri_vien_id'
        )->withTimestamps();
    }

    public function duLieuHuanLuyenChatbotsDaDuyet(): HasMany
    {
        return $this->hasMany(DuLieuHuanLuyenChatbot::class, 'nguoi_duyet_id');
    }

    public function hasRoutePermission(string $routeName): bool
    {
        if ((int) $this->is_master === 1) {
            return true;
        }

        return $this->quyenQuanTriViens()
            ->whereHas('chucNangs', function ($query) use ($routeName) {
                $query->where('route_name', $routeName);
            })
            ->exists();
    }
}