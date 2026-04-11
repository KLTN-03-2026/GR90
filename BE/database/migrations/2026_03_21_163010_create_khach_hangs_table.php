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
        Schema::create('khach_hangs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_cong_khai', 8)->unique();
            $table->string('ma_khach_hang', 30)->unique();
            $table->string('ten_dang_nhap', 100)->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('ho_ten', 150);
            $table->string('email', 150)->unique();
            $table->string('so_dien_thoai', 20)->nullable();
            $table->string('anh_dai_dien', 255)->nullable();
            $table->tinyInteger('trang_thai')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khach_hangs');
    }
};
