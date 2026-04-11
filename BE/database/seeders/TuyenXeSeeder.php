<?php

namespace Database\Seeders;

use App\Models\DonViVanHanh;
use App\Models\LoaiTuyen;
use App\Models\TrangThaiTuyen;
use App\Models\TuyenXe;
use Database\Seeders\Concerns\InteractsWithBusMapApi;
use Illuminate\Database\Seeder;

class TuyenXeSeeder extends Seeder
{
    use InteractsWithBusMapApi;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loaiTuyenIds = LoaiTuyen::query()->pluck('id', 'ma_loai_tuyen');
        $trangThaiIds = TrangThaiTuyen::query()->pluck('id', 'ma_trang_thai');
        $donViVanHanhIds = DonViVanHanh::query()->pluck('id', 'ma_don_vi');

        $routes = $this->busMapRouteList('dn');

        foreach ($routes as $route) {
            $routeId = (int) ($route['routeId'] ?? 0);
            if ($routeId <= 0) {
                continue;
            }

            $routeNoRaw = $this->busMapNormalizeText($route['routeNo'] ?? '');
            $maTuyen = $this->formatMaTuyen($routeNoRaw, $routeId);

            $detail = $this->busMapRouteDetail($routeId, 'dn');
            [$diemDau, $diemCuoi] = $this->extractStartEndPoints($detail, $route['routeName'] ?? '');

            $maLoai = $this->resolveLoaiTuyenCode($routeNoRaw, $route['routeName'] ?? '');
            $maTrangThai = $this->resolveTrangThaiCode($routeNoRaw, $route, $detail);
            $maDonVi = $this->resolveDonViVanHanhCode($routeNoRaw);

            [$gioBatDau, $gioKetThuc] = $this->parseOperationTime($route['operationTime'] ?? null);
            $giaVeLuot = $this->parseTicketToNumber($route['normalTicket'] ?? null);

            TuyenXe::query()->updateOrCreate(
                ['ma_tuyen' => $maTuyen],
                [
                    'ten_tuyen' => $this->normalizeRouteName($route['routeName'] ?? $maTuyen),
                    'loai_tuyen_id' => $loaiTuyenIds[$maLoai] ?? $loaiTuyenIds['DN_LT_KHONG_TRO_GIA'] ?? null,
                    'trang_thai_tuyen_id' => $trangThaiIds[$maTrangThai] ?? $trangThaiIds['DN_TT_DANG_HOAT_DONG'] ?? null,
                    'don_vi_van_hanh_id' => $maDonVi ? ($donViVanHanhIds[$maDonVi] ?? null) : null,
                    'diem_dau' => $diemDau,
                    'diem_cuoi' => $diemCuoi,
                    'thoi_gian_bat_dau_hoat_dong' => $gioBatDau,
                    'thoi_gian_ket_thuc_hoat_dong' => $gioKetThuc,
                    'gia_ve_luot' => $giaVeLuot,
                    'nguon_du_lieu' => 'https://api-web.busmap.vn/web/public/route/list?regionCode=dn',
                    'ghi_chu' => 'Đồng bộ từ BusMap routeId=' . $routeId,
                ]
            );
        }
    }

    private function normalizeRouteName(string $routeName): string
    {
        $name = trim($routeName);

        return preg_replace('/\s+/', ' ', $name) ?: $routeName;
    }

    private function formatMaTuyen(string $routeNo, int $routeId): string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $routeNo));

        if ($clean === '') {
            return 'DNR' . $routeId;
        }

        return 'DN' . $clean;
    }

    private function resolveLoaiTuyenCode(string $routeNo, string $routeName): string
    {
        $routeNoUpper = strtoupper($routeNo);
        $routeNameLower = mb_strtolower($routeName);

        if (str_starts_with($routeNoUpper, 'LK')) {
            return 'DN_LT_LIEN_KE';
        }

        if (str_contains($routeNoUpper, 'DL') || str_contains($routeNameLower, 'du lịch')) {
            return 'DN_LT_DU_LICH';
        }

        $troGiaSet = ['04', '05', '07', '08', '10', '11', '12', '15', '16', '17'];
        if (in_array($routeNoUpper, $troGiaSet, true)) {
            return 'DN_LT_TRO_GIA';
        }

        return 'DN_LT_KHONG_TRO_GIA';
    }

    private function resolveTrangThaiCode(string $routeNo, array $route, array $detail): string
    {
        $routeNoUpper = strtoupper($routeNo);

        if (in_array($routeNoUpper, ['16', '17'], true)) {
            return 'DN_TT_THU_TUC_VAN_HANH';
        }

        $disabled = (bool) ($route['disabled'] ?? false);
        $temporaryClosed = (bool) ($detail['temporaryClosed'] ?? false);
        if ($disabled || $temporaryClosed || in_array($routeNoUpper, ['04', '10', '15'], true)) {
            return 'DN_TT_TAM_DUNG';
        }

        return 'DN_TT_DANG_HOAT_DONG';
    }

    private function resolveDonViVanHanhCode(string $routeNo): ?string
    {
        $routeNoUpper = strtoupper($routeNo);

        if (in_array($routeNoUpper, ['05', '07', '08', '11', '12'], true)) {
            return 'DN_DV_PT';
        }

        if (in_array($routeNoUpper, ['04', '10', '15', '16', '17'], true)) {
            return 'DN_DV_QA1';
        }

        return null;
    }

    private function parseOperationTime(?string $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [null, null];
        }

        $parts = preg_split('/\s*-\s*/', trim($value));
        if (! is_array($parts) || count($parts) !== 2) {
            return [null, null];
        }

        $start = $this->toSqlTime($parts[0]);
        $end = $this->toSqlTime($parts[1]);

        return [$start, $end];
    }

    private function toSqlTime(string $value): ?string
    {
        if (! preg_match('/^\d{1,2}:\d{2}$/', trim($value))) {
            return null;
        }

        [$hour, $minute] = explode(':', trim($value));

        return sprintf('%02d:%02d:00', (int) $hour, (int) $minute);
    }

    private function parseTicketToNumber(?string $value): ?float
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $value);
        if (! $digits) {
            return null;
        }

        return (float) $digits;
    }

    private function extractStartEndPoints(array $detail, string $fallbackName): array
    {
        $stations = is_array($detail['stations'] ?? null) ? $detail['stations'] : [];
        $outbound = array_values(array_filter($stations, fn ($s) => (int) ($s['stationDirection'] ?? 0) === 0));

        usort($outbound, fn ($a, $b) => (int) ($a['stationOrder'] ?? 0) <=> (int) ($b['stationOrder'] ?? 0));

        $start = trim((string) ($outbound[0]['stationName'] ?? ''));
        $end = trim((string) ($outbound[count($outbound) - 1]['stationName'] ?? ''));

        if ($start !== '' && $end !== '') {
            return [$start, $end];
        }

        $parts = preg_split('/\s*-\s*/', $fallbackName, 2);
        if (is_array($parts) && count($parts) === 2) {
            return [trim($parts[0]), trim($parts[1])];
        }

        return ['Chưa cập nhật', 'Chưa cập nhật'];
    }
}
