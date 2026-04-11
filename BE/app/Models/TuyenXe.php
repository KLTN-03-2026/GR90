<?php

namespace App\Models;

use App\Models\Concerns\HasMaCongKhai;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TuyenXe extends Model
{
    use HasFactory, HasMaCongKhai;

    protected $table = 'tuyen_xes';

    protected $fillable = [
        'ma_cong_khai',
        'ma_tuyen',
        'ten_tuyen',
        'ten_tuyen_cu',
        'loai_tuyen_id',
        'trang_thai_tuyen_id',
        'don_vi_van_hanh_id',
        'diem_dau',
        'diem_cuoi',
        'thoi_gian_bat_dau_hoat_dong',
        'thoi_gian_ket_thuc_hoat_dong',
        'tan_suat_phut',
        'tan_suat_cao_diem_phut',
        'tan_suat_thap_diem_phut',
        'cu_ly_km',
        'gia_ve_luot',
        'ngay_bat_dau_van_hanh',
        'ghi_chu',
        'nguon_du_lieu',
    ];

    protected $casts = [
        'thoi_gian_bat_dau_hoat_dong' => 'datetime:H:i:s',
        'thoi_gian_ket_thuc_hoat_dong' => 'datetime:H:i:s',
        'tan_suat_phut' => 'integer',
        'tan_suat_cao_diem_phut' => 'integer',
        'tan_suat_thap_diem_phut' => 'integer',
        'cu_ly_km' => 'decimal:2',
        'gia_ve_luot' => 'decimal:2',
        'ngay_bat_dau_van_hanh' => 'date',
    ];

    public function loaiTuyen(): BelongsTo
    {
        return $this->belongsTo(LoaiTuyen::class, 'loai_tuyen_id');
    }

    public function trangThaiTuyen(): BelongsTo
    {
        return $this->belongsTo(TrangThaiTuyen::class, 'trang_thai_tuyen_id');
    }

    public function donViVanHanh(): BelongsTo
    {
        return $this->belongsTo(DonViVanHanh::class, 'don_vi_van_hanh_id');
    }

    public function loTrinhTuyens(): HasMany
    {
        return $this->hasMany(LoTrinhTuyen::class, 'tuyen_xe_id');
    }

    public function giaVeTuyens(): HasMany
    {
        return $this->hasMany(GiaVeTuyen::class, 'tuyen_xe_id');
    }

    public function tuyenYeuThichs(): HasMany
    {
        return $this->hasMany(TuyenYeuThich::class, 'tuyen_xe_id');
    }

    public function luotXemTuyens(): HasMany
    {
        return $this->hasMany(LuotXemTuyen::class, 'tuyen_xe_id');
    }

    public function tuKhoaTuyenXes(): HasMany
    {
        return $this->hasMany(TuKhoaTuyenXe::class, 'tuyen_xe_id');
    }

    public function xeBuyts(): HasMany
    {
        return $this->hasMany(XeBuyt::class, 'tuyen_xe_id');
    }
}
