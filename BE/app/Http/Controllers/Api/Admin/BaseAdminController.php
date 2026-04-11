<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\ApiRequestException;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

abstract class BaseAdminController extends Controller
{
    use ApiResponseTrait;

    protected function handle(callable $callback)
    {
        try {
            return $callback();
        } catch (ApiRequestException $exception) {
            throw $exception;
        } catch (ModelNotFoundException $exception) {
            throw new ApiRequestException('Không tìm thấy dữ liệu cần thao tác.');
        } catch (Throwable $exception) {
            throw new ApiRequestException('Thao tác thất bại.', [
                'chi_tiet' => $exception->getMessage(),
            ]);
        }
    }
}