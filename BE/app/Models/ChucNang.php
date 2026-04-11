<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChucNang extends Model
{
    use HasFactory;

    protected $table = 'chuc_nangs';

    protected $fillable = [
        'ma_chuc_nang',
        'ten_chuc_nang',
        'route_name',
        'uri',
        'http_method',
        'nhom',
        'mo_ta',
    ];

    public function phanQuyenQuanTriViens(): BelongsToMany
    {
        return $this->belongsToMany(
            PhanQuyenQuanTriVien::class,
            'chi_tiet_phan_quyens',
            'chuc_nang_id',
            'phan_quyen_quan_tri_vien_id'
        )->withTimestamps();
    }
}