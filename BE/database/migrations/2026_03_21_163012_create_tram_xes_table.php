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
        Schema::create('tram_xes', function (Blueprint $table) {
            $table->id();
            $table->string('ma_cong_khai', 8)->unique();
            $table->string('ma_tram', 30)->unique();
            $table->string('ten_tram', 255);
            $table->string('dia_chi', 255)->nullable();
            $table->decimal('vi_do', 10, 7)->nullable();
            $table->decimal('kinh_do', 10, 7)->nullable();
            $table->string('khu_vuc', 150)->nullable();
            $table->timestamps();

            $table->index(['vi_do', 'kinh_do'], 'idx_tram_toa_do');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tram_xes');
    }
};
