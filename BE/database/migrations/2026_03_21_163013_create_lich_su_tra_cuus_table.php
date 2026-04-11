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
        Schema::create('lich_su_tra_cuus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khach_hang_id')
                ->nullable()
                ->constrained('khach_hangs')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('diem_di', 255)->nullable();
            $table->string('diem_den', 255)->nullable();
            $table->string('tu_khoa_tim_kiem', 255)->nullable();
            $table->json('ket_qua_goi_y_json')->nullable();
            $table->dateTime('thoi_gian_tra_cuu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lich_su_tra_cuus');
    }
};
