<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuKhoaTuyenXe extends Model
{
    use HasFactory;

    protected $table = 'tu_khoa_tuyen_xes';

    protected $fillable = [
        'tuyen_xe_id',
        'tu_khoa',
    ];

    public function tuyenXe(): BelongsTo
    {
        return $this->belongsTo(TuyenXe::class, 'tuyen_xe_id');
    }
}
