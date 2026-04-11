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
        Schema::create('tuyen_xes', function (Blueprint $table) {
            $table->id();
            $table->string('ma_cong_khai', 8)->unique();
            $table->string('ma_tuyen', 30)->unique();
            $table->string('ten_tuyen', 255);
            $table->string('ten_tuyen_cu', 255)->nullable();
            $table->foreignId('loai_tuyen_id')
                ->constrained('loai_tuyens')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('trang_thai_tuyen_id')
                ->constrained('trang_thai_tuyens')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('don_vi_van_hanh_id')
                ->nullable()
                ->constrained('don_vi_van_hanhs')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('diem_dau', 255);
            $table->string('diem_cuoi', 255);
            $table->time('thoi_gian_bat_dau_hoat_dong')->nullable();
            $table->time('thoi_gian_ket_thuc_hoat_dong')->nullable();
            $table->integer('tan_suat_phut')->nullable();
            $table->integer('tan_suat_cao_diem_phut')->nullable();
            $table->integer('tan_suat_thap_diem_phut')->nullable();
            $table->decimal('cu_ly_km', 8, 2)->nullable();
            $table->decimal('gia_ve_luot', 12, 2)->nullable();
            $table->date('ngay_bat_dau_van_hanh')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->string('nguon_du_lieu', 255)->nullable();
            $table->timestamps();

            $table->index(['loai_tuyen_id', 'trang_thai_tuyen_id'], 'idx_tuyen_loai_trang_thai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuyen_xes');
    }
};
