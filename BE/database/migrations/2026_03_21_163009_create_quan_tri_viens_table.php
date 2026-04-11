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
        Schema::create('quan_tri_viens', function (Blueprint $table) {
            $table->id();
            $table->string('ma_cong_khai', 8)->unique();
            $table->string('ma_quan_tri_vien', 30)->unique();
            $table->string('ten_dang_nhap', 100)->unique();
            $table->string('password');
            $table->string('ho_ten', 150);
            $table->string('email', 150)->unique();
            $table->string('so_dien_thoai', 20)->nullable();
            $table->tinyInteger('trang_thai')->default(1);
            $table->dateTime('lan_dang_nhap_cuoi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quan_tri_viens');
    }
};
