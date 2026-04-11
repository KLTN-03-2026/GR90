<?php

namespace App\Http\Middleware;

use App\Models\KhachHang;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof KhachHang) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập khu vực người dùng.',
            ], 403);
        }

        if ((int) $user->trang_thai !== 1) {
            $user->currentAccessToken()?->delete();

            return response()->json([
                'success' => false,
                'message' => 'Tài khoản khách hàng đã bị khóa.',
            ], 403);
        }

        return $next($request);
    }
}