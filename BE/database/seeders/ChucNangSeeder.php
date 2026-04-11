<?php

namespace Database\Seeders;

use App\Models\ChucNang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ChucNangSeeder extends Seeder
{
    protected array $excludedRouteNames = [
        'admin.auth.login',
        'admin.auth.logout',
        'admin.auth.me',
        'admin.auth.profile',
        'admin.auth.password',
    ];

    public function run(): void
    {
        foreach (Route::getRoutes() as $route) {
            $routeName = (string) $route->getName();

            if ($routeName === '' || ! str_starts_with($routeName, 'admin.')) {
                continue;
            }

            if (in_array($routeName, $this->excludedRouteNames, true)) {
                continue;
            }

            $methods = array_values(array_filter(
                $route->methods(),
                fn (string $method) => ! in_array($method, ['HEAD', 'OPTIONS'], true)
            ));

            if ($methods === []) {
                continue;
            }

            ChucNang::query()->updateOrCreate(
                ['route_name' => $routeName],
                [
                    'ma_chuc_nang' => $this->buildCode($routeName),
                    'ten_chuc_nang' => $this->buildName($routeName),
                    'uri' => $route->uri(),
                    'http_method' => implode('|', $methods),
                    'nhom' => $this->buildGroup($routeName),
                    'mo_ta' => 'Chức năng quyền cho route ' . $routeName,
                ]
            );
        }
    }

    protected function buildCode(string $routeName): string
    {
        return Str::upper(str_replace(['.', '-'], '_', $routeName));
    }

    protected function buildGroup(string $routeName): string
    {
        $segments = explode('.', $routeName);
        $main = $segments[1] ?? 'admin';
        $resource = $segments[2] ?? 'tong-quat';

        return Str::headline($main . ' ' . $resource);
    }

    protected function buildName(string $routeName): string
    {
        $segments = explode('.', $routeName);
        $resource = Str::headline(str_replace('-', ' ', $segments[2] ?? 'chức năng'));
        $action = $segments[3] ?? 'index';

        $actionLabels = [
            'index' => 'Xem danh sách',
            'show' => 'Xem chi tiết',
            'store' => 'Tạo mới',
            'update' => 'Cập nhật',
            'destroy' => 'Xóa',
            'permission-options' => 'Lấy danh sách quyền',
        ];

        return ($actionLabels[$action] ?? Str::headline($action)) . ' ' . $resource;
    }
}