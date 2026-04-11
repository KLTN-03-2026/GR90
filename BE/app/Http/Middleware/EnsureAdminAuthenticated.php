<?php

namespace App\Http\Middleware;

use App\Models\QuanTriVien;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof QuanTriVien) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập khu vực quản trị.',
            ], 403);
        }

        if ((int) $user->trang_thai !== 1) {
            $user->currentAccessToken()?->delete();

            return response()->json([
                'success' => false,
                'message' => 'Tài khoản quản trị viên đã bị khóa.',
            ], 403);
        }

        return $next($request);
    }
}