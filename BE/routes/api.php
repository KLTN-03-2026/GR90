<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\ChiTietLoTrinhController;
use App\Http\Controllers\Api\Admin\DonViVanHanhController;
use App\Http\Controllers\Api\Admin\GiaVeTuyenController;
use App\Http\Controllers\Api\Admin\KhachHangController;
use App\Http\Controllers\Api\Admin\LoaiTuyenController;
use App\Http\Controllers\Api\Admin\LoTrinhTuyenController;
use App\Http\Controllers\Api\Admin\PhanQuyenQuanTriVienController;
use App\Http\Controllers\Api\Admin\QuanTriVienController;
use App\Http\Controllers\Api\Admin\TramXeController;
use App\Http\Controllers\Api\Admin\TrangThaiTuyenController;
use App\Http\Controllers\Api\Admin\TuyenXeController;
use App\Http\Controllers\Api\Admin\XeBuytController;
use App\Http\Controllers\Api\Client\ClientFavoriteController;
use App\Http\Controllers\Api\Client\ClientRouteController;
use App\Http\Controllers\Api\Client\ClientSearchHistoryController;
use App\Http\Controllers\Api\Client\ClientStopController;
use App\Http\Controllers\Api\Client\ClientViewedRouteController;
use App\Http\Controllers\Api\Client\ClientXeBuytController;
use App\Http\Controllers\Api\ClientAuthController;
use App\Http\Controllers\Api\ClientLandingMapController;
use App\Http\Controllers\Api\MapProxyController;
use Illuminate\Support\Facades\Route;

Route::get('/map-proxy', MapProxyController::class);
Route::get('/client/landing-map', ClientLandingMapController::class);
Route::prefix('client')->as('client.')->group(function () {
    Route::get('routes', [ClientRouteController::class, 'index'])->name('routes.index');
    Route::get('routes/recommend', [ClientRouteController::class, 'recommend'])->name('routes.recommend');
    Route::get('routes/{id}/geometry', [ClientRouteController::class, 'geometry'])->name('routes.geometry');
    Route::get('routes/{id}', [ClientRouteController::class, 'show'])->name('routes.show');
    Route::post('routes/{id}/record-view', [ClientRouteController::class, 'recordView'])->name('routes.record-view');
    Route::get('routes/{id}/buses', [ClientXeBuytController::class, 'byRoute'])->name('routes.buses');
    Route::get('routes/{id}/schedule', [ClientXeBuytController::class, 'schedule'])->name('routes.schedule');
    Route::get('stops/nearby', [ClientStopController::class, 'nearby'])->name('stops.nearby');
    Route::get('stops/suggestions', [ClientStopController::class, 'suggestions'])->name('stops.suggestions');
    Route::get('stops/reachable-from/{id}', [ClientStopController::class, 'reachableFrom'])->name('stops.reachable-from');
    Route::get('stops/{id}', [ClientStopController::class, 'show'])->name('stops.show');
    Route::get('stops/{id}/buses', [ClientXeBuytController::class, 'byStop'])->name('stops.buses');
    Route::get('buses/live', [ClientXeBuytController::class, 'liveAll'])->name('buses.live');

    Route::prefix('auth')->as('auth.')->group(function () {
        Route::post('register', [ClientAuthController::class, 'register'])->name('register');
        Route::post('login', [ClientAuthController::class, 'login'])->name('login');
    });

    Route::middleware(['auth:sanctum', 'client'])->prefix('auth')->as('auth.')->group(function () {
        Route::get('me', [ClientAuthController::class, 'me'])->name('me');
        Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');
        Route::put('profile', [ClientAuthController::class, 'updateProfile'])->name('profile');
        Route::put('password', [ClientAuthController::class, 'updatePassword'])->name('password');
    });

    Route::middleware(['auth:sanctum', 'client'])->prefix('me')->as('me.')->group(function () {
        Route::get('favorites', [ClientFavoriteController::class, 'index'])->name('favorites.index');
        Route::post('favorites/{routeId}', [ClientFavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('favorites/{routeId}', [ClientFavoriteController::class, 'destroy'])->name('favorites.destroy');

        Route::get('search-histories', [ClientSearchHistoryController::class, 'index'])->name('search-histories.index');
        Route::post('search-histories', [ClientSearchHistoryController::class, 'store'])->name('search-histories.store');
        Route::delete('search-histories/{id}', [ClientSearchHistoryController::class, 'destroy'])->name('search-histories.destroy');
        Route::delete('search-histories', [ClientSearchHistoryController::class, 'clear'])->name('search-histories.clear');

        Route::get('viewed-routes', [ClientViewedRouteController::class, 'index'])->name('viewed-routes.index');
    });
});

Route::prefix('admin')->as('admin.')->group(function () {
    Route::prefix('auth')->as('auth.')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
    });

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::prefix('auth')->as('auth.')->group(function () {
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::put('profile', [AuthController::class, 'updateProfile'])->name('profile');
            Route::put('password', [AuthController::class, 'updatePassword'])->name('password');
        });

        Route::middleware('admin.permission')->group(function () {
            Route::prefix('tai-khoan')->as('tai-khoan.')->group(function () {
                Route::get('phan-quyens/chuc-nang-options', [PhanQuyenQuanTriVienController::class, 'functionOptions'])
                    ->name('phan-quyens.chuc-nang-options');

                Route::apiResource('phan-quyens', PhanQuyenQuanTriVienController::class)
                    ->parameters(['phan-quyens' => 'phan_quyen'])
                    ->names('phan-quyens');

                Route::get('quan-tri-viens/permission-options', [QuanTriVienController::class, 'permissionOptions'])
                    ->name('quan-tri-viens.permission-options');

                Route::apiResource('quan-tri-viens', QuanTriVienController::class)
                    ->parameters(['quan-tri-viens' => 'quan_tri_vien'])
                    ->names('quan-tri-viens');

                Route::apiResource('khach-hangs', KhachHangController::class)
                    ->parameters(['khach-hangs' => 'khach_hang'])
                    ->names('khach-hangs');
            });

            Route::prefix('danh-muc')->as('danh-muc.')->group(function () {
                Route::apiResource('loai-tuyens', LoaiTuyenController::class)
                    ->parameters(['loai-tuyens' => 'loai_tuyen'])
                    ->names('loai-tuyens');

                Route::apiResource('trang-thai-tuyens', TrangThaiTuyenController::class)
                    ->parameters(['trang-thai-tuyens' => 'trang_thai_tuyen'])
                    ->names('trang-thai-tuyens');

                Route::apiResource('don-vi-van-hanhs', DonViVanHanhController::class)
                    ->parameters(['don-vi-van-hanhs' => 'don_vi_van_hanh'])
                    ->names('don-vi-van-hanhs');

                Route::apiResource('tram-xes', TramXeController::class)
                    ->parameters(['tram-xes' => 'tram_xe'])
                    ->names('tram-xes');
            });

            Route::prefix('van-hanh')->as('van-hanh.')->group(function () {
                Route::apiResource('tuyen-xes', TuyenXeController::class)
                    ->parameters(['tuyen-xes' => 'tuyen_xe'])
                    ->names('tuyen-xes');

                Route::apiResource('lo-trinh-tuyens', LoTrinhTuyenController::class)
                    ->parameters(['lo-trinh-tuyens' => 'lo_trinh_tuyen'])
                    ->names('lo-trinh-tuyens');

                Route::apiResource('gia-ve-tuyens', GiaVeTuyenController::class)
                    ->parameters(['gia-ve-tuyens' => 'gia_ve_tuyen'])
                    ->names('gia-ve-tuyens');

                Route::apiResource('chi-tiet-lo-trinhs', ChiTietLoTrinhController::class)
                    ->parameters(['chi-tiet-lo-trinhs' => 'chi_tiet_lo_trinh'])
                    ->names('chi-tiet-lo-trinhs');

                Route::apiResource('xe-buyts', XeBuytController::class)
                    ->parameters(['xe-buyts' => 'xe_buyt'])
                    ->names('xe-buyts');
            });
        });
    });
});
