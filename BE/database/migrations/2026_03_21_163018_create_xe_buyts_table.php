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
        Schema::create('xe_buyts', function (Blueprint $table) {
            $table->id();
            $table->string('bien_so', 20)->unique();
            $table->string('ten_xe', 255);
            $table->foreignId('tuyen_xe_id')
                ->constrained('tuyen_xes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('trang_thai', 50)->default('dang_hoat_dong');
            $table->string('loai_xe', 100)->nullable();
            $table->integer('so_cho')->nullable();
            $table->year('nam_san_xuat')->nullable();
            $table->date('ngay_bat_dau_van_hanh')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->index('trang_thai', 'idx_xe_trang_thai');
            $table->index('tuyen_xe_id', 'idx_xe_tuyen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xe_buyts');
    }
};
