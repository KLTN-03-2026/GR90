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
        Schema::create('hoi_thoai_chatbots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khach_hang_id')
                ->nullable()
                ->constrained('khach_hangs')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('ma_phien', 64)->unique();
            $table->string('kenh', 50)->default('web');
            $table->dateTime('bat_dau_luc');
            $table->dateTime('ket_thuc_luc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoi_thoai_chatbots');
    }
};
