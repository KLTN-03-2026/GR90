<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasMaCongKhai
{
    protected static function bootHasMaCongKhai(): void
    {
        static::creating(function (Model $model): void {
            $column = $model->maCongKhaiColumn();

            if (empty($model->{$column})) {
                $model->{$column} = static::taoMaCongKhaiKhongTrung($column);
            }
        });
    }

    protected function maCongKhaiColumn(): string
    {
        return 'ma_cong_khai';
    }

    protected static function taoMaCongKhaiKhongTrung(string $column): string
    {
        do {
            $ma = 'DN' . Str::upper(Str::random(6));
        } while (static::query()->where($column, $ma)->exists());

        return $ma;
    }
}
