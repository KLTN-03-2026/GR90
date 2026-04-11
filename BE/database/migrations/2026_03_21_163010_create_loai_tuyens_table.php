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
        Schema::create('loai_tuyens', function (Blueprint $table) {
            $table->id();
            $table->string('ma_loai_tuyen', 30)->unique();
            $table->string('ten_loai_tuyen', 150);
            $table->string('mo_ta', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loai_tuyens');
    }
};
