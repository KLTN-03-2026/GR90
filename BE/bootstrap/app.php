<?php

use App\Exceptions\ApiRequestException;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsureAdminHasPermission;
use App\Http\Middleware\EnsureClientAuthenticated;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdminAuthenticated::class,
            'admin.permission' => EnsureAdminHasPermission::class,
            'client' => EnsureClientAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! $request->is('api/admin/*') && ! $request->is('api/client/*')) {
                return null;
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để tiếp tục.',
                ], 401);
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu gửi lên không hợp lệ.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof ApiRequestException) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'errors' => $exception->getErrors(),
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Xử lý thất bại.',
                'errors' => [
                    'chi_tiet' => $exception->getMessage(),
                ],
            ], 422);
        });
    })->create();
