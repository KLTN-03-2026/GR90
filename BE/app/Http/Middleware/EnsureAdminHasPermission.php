<?php

namespace App\Http\Middleware;

use App\Models\QuanTriVien;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = (string) $request->route()?->getName();

        if (! $user instanceof QuanTriVien) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập khu vực quản trị.',
            ], 403);
        }

        if ((int) $user->is_master === 1) {
            return $next($request);
        }

        if ($routeName === '') {
            return response()->json([
                'success' => false,
                'message' => 'Không xác định được chức năng cần kiểm tra quyền.',
            ], 403);
        }

        if (! $user->hasRoutePermission($routeName)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện chức năng này.',
            ], 403);
        }

        return $next($request);
    }
}