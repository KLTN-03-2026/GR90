<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuanTriVienQuyen extends Model
{
    use HasFactory;

    protected $table = 'quan_tri_vien_quyens';

    protected $fillable = [
        'quan_tri_vien_id',
        'phan_quyen_quan_tri_vien_id',
    ];

    public function quanTriVien(): BelongsTo
    {
        return $this->belongsTo(QuanTriVien::class, 'quan_tri_vien_id');
    }

    public function phanQuyenQuanTriVien(): BelongsTo
    {
        return $this->belongsTo(PhanQuyenQuanTriVien::class, 'phan_quyen_quan_tri_vien_id');
    }
}
