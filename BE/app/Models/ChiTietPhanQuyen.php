<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietPhanQuyen extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_phan_quyens';

    protected $fillable = [
        'phan_quyen_quan_tri_vien_id',
        'chuc_nang_id',
    ];

    public function phanQuyenQuanTriVien(): BelongsTo
    {
        return $this->belongsTo(PhanQuyenQuanTriVien::class, 'phan_quyen_quan_tri_vien_id');
    }

    public function chucNang(): BelongsTo
    {
        return $this->belongsTo(ChucNang::class, 'chuc_nang_id');
    }
}