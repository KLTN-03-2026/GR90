<?php

namespace App\Http\Controllers\Api\Client;

use App\Exceptions\ApiRequestException;
use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use App\Models\LoTrinhTuyen;
use App\Models\TramXe;
use App\Models\TuyenXe;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

abstract class BaseClientController extends Controller
{
    use ApiResponseTrait;

    protected function handle(callable $callback)
    {
        try {
            return $callback();
        } catch (ApiRequestException $exception) {
            throw $exception;
        } catch (ModelNotFoundException $exception) {
            throw new ApiRequestException('Khong tim thay du lieu can thao tac.');
        } catch (Throwable $exception) {
            throw new ApiRequestException('Thao tac that bai.', [
                'chi_tiet' => $exception->getMessage(),
            ]);
        }
    }

    protected function resolvePerPage(Request $request, int $default = 12, int $max = 50): int
    {
        $value = (int) $request->integer('per_page', $default);

        if ($value < 1) {
            return $default;
        }

        return min($value, $max);
    }

    protected function routeSummary(TuyenXe $tuyenXe, ?KhachHang $khachHang = null): array
    {
        $tongDiemDung = 0;
        $huongDi = [];

        foreach ($tuyenXe->loTrinhTuyens ?? [] as $loTrinhTuyen) {
            $tongDiemDung += (int) ($loTrinhTuyen->chi_tiet_lo_trinhs_count ?? 0);
            $huongDi[] = $this->routeDirectionSummary($loTrinhTuyen);
        }

        return [
            'id' => $tuyenXe->id,
            'ma_cong_khai' => $tuyenXe->ma_cong_khai,
            'ma_tuyen' => $tuyenXe->ma_tuyen,
            'ten_tuyen' => $tuyenXe->ten_tuyen,
            'ten_tuyen_cu' => $tuyenXe->ten_tuyen_cu,
            'loai_tuyen' => $tuyenXe->loaiTuyen?->ten_loai_tuyen,
            'trang_thai' => $tuyenXe->trangThaiTuyen?->ten_trang_thai,
            'don_vi_van_hanh' => $tuyenXe->donViVanHanh?->ten_don_vi,
            'diem_dau' => $tuyenXe->diem_dau,
            'diem_cuoi' => $tuyenXe->diem_cuoi,
            'thoi_gian_bat_dau_hoat_dong' => optional($tuyenXe->thoi_gian_bat_dau_hoat_dong)->format('H:i'),
            'thoi_gian_ket_thuc_hoat_dong' => optional($tuyenXe->thoi_gian_ket_thuc_hoat_dong)->format('H:i'),
            'tan_suat_phut' => $tuyenXe->tan_suat_phut,
            'cu_ly_km' => $tuyenXe->cu_ly_km,
            'gia_ve_luot' => $tuyenXe->gia_ve_luot,
            'ghi_chu' => $tuyenXe->ghi_chu,
            'tong_diem_dung' => $tongDiemDung,
            'tong_luot_xem' => (int) ($tuyenXe->luot_xem_tuyens_count ?? 0),
            'is_favorite' => $khachHang ? (bool) ($tuyenXe->is_favorite ?? false) : false,
            'huong_di' => $huongDi,
        ];
    }

    protected function routeDetail(TuyenXe $tuyenXe, ?KhachHang $khachHang = null): array
    {
        return array_merge($this->routeSummary($tuyenXe, $khachHang), [
            'ngay_bat_dau_van_hanh' => optional($tuyenXe->ngay_bat_dau_van_hanh)->format('d/m/Y'),
            'gia_ve' => collect($tuyenXe->giaVeTuyens ?? [])->map(function ($giaVe) {
                return [
                    'id' => $giaVe->id,
                    'loai_gia_ve' => $giaVe->loai_gia_ve,
                    'so_tien' => $giaVe->so_tien,
                    'don_vi_tien_te' => $giaVe->don_vi_tien_te,
                    'ghi_chu' => $giaVe->ghi_chu,
                ];
            })->values()->all(),
            'tu_khoa' => collect($tuyenXe->tuKhoaTuyenXes ?? [])->pluck('tu_khoa')->filter()->values()->all(),
            'lo_trinh_tuyens' => collect($tuyenXe->loTrinhTuyens ?? [])->map(function ($loTrinhTuyen) {
                return [
                    'id' => $loTrinhTuyen->id,
                    'chieu' => $loTrinhTuyen->chieu,
                    'mo_ta_lo_trinh' => $loTrinhTuyen->mo_ta_lo_trinh,
                    'chi_tiet_lo_trinhs' => collect($loTrinhTuyen->chiTietLoTrinhs ?? [])->map(function ($chiTiet) {
                        return [
                            'id' => $chiTiet->id,
                            'thu_tu_dung' => $chiTiet->thu_tu_dung,
                            'ten_diem_di_qua' => $chiTiet->ten_diem_di_qua,
                            'thoi_gian_dung_du_kien_giay' => $chiTiet->thoi_gian_dung_du_kien_giay,
                            'khoang_cach_tu_diem_truoc_met' => $chiTiet->khoang_cach_tu_diem_truoc_met,
                            'tram_xe' => $chiTiet->tramXe ? $this->stopSummary($chiTiet->tramXe) : null,
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ]);
    }

    protected function stopSummary(TramXe $tramXe): array
    {
        return [
            'id' => $tramXe->id,
            'ma_cong_khai' => $tramXe->ma_cong_khai,
            'ma_tram' => $tramXe->ma_tram,
            'ten_tram' => $tramXe->ten_tram,
            'dia_chi' => $tramXe->dia_chi,
            'khu_vuc' => $tramXe->khu_vuc,
            'vi_do' => (float) $tramXe->vi_do,
            'kinh_do' => (float) $tramXe->kinh_do,
        ];
    }

    protected function stopDetail(TramXe $tramXe): array
    {
        $tuyenDiQua = collect($tramXe->chiTietLoTrinhs ?? [])
            ->map(fn ($chiTiet) => $chiTiet->loTrinhTuyen?->tuyenXe)
            ->filter()
            ->unique('id')
            ->values()
            ->map(function (TuyenXe $tuyenXe) {
                return [
                    'id' => $tuyenXe->id,
                    'ma_tuyen' => $tuyenXe->ma_tuyen,
                    'ten_tuyen' => $tuyenXe->ten_tuyen,
                    'loai_tuyen' => $tuyenXe->loaiTuyen?->ten_loai_tuyen,
                    'trang_thai' => $tuyenXe->trangThaiTuyen?->ten_trang_thai,
                    'diem_dau' => $tuyenXe->diem_dau,
                    'diem_cuoi' => $tuyenXe->diem_cuoi,
                ];
            })
            ->all();

        return array_merge($this->stopSummary($tramXe), [
            'tong_tuyen_di_qua' => count($tuyenDiQua),
            'tuyen_di_qua' => $tuyenDiQua,
        ]);
    }

    protected function routeDirectionSummary(LoTrinhTuyen $loTrinhTuyen): array
    {
        return [
            'id' => $loTrinhTuyen->id,
            'chieu' => $loTrinhTuyen->chieu,
            'tong_diem_dung' => (int) ($loTrinhTuyen->chi_tiet_lo_trinhs_count ?? 0),
        ];
    }
}
