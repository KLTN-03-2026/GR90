<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chi_tiet_lo_trinhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lo_trinh_tuyen_id')
                ->constrained('lo_trinh_tuyens')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('tram_xe_id')
                ->nullable()
                ->constrained('tram_xes')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->integer('thu_tu_dung');
            $table->string('ten_diem_di_qua', 255)->nullable();
            $table->integer('thoi_gian_dung_du_kien_giay')->nullable();
            $table->integer('khoang_cach_tu_diem_truoc_met')->nullable();
            $table->timestamps();

            $table->index(['lo_trinh_tuyen_id', 'thu_tu_dung'], 'idx_chi_tiet_thu_tu');
            $table->unique(['lo_trinh_tuyen_id', 'thu_tu_dung'], 'uq_chi_tiet_thu_tu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_lo_trinhs');
    }
};
