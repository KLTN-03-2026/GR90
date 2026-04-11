<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PhanQuyenQuanTriVien extends Model
{
    use HasFactory;

    protected $table = 'phan_quyen_quan_tri_viens';

    protected $fillable = [
        'ten_quyen',
        'mo_ta',
    ];

    public function quanTriViens(): BelongsToMany
    {
        return $this->belongsToMany(
            QuanTriVien::class,
            'quan_tri_vien_quyens',
            'phan_quyen_quan_tri_vien_id',
            'quan_tri_vien_id'
        )->withTimestamps();
    }

    public function chucNangs(): BelongsToMany
    {
        return $this->belongsToMany(
            ChucNang::class,
            'chi_tiet_phan_quyens',
            'phan_quyen_quan_tri_vien_id',
            'chuc_nang_id'
        )->withTimestamps();
    }
}