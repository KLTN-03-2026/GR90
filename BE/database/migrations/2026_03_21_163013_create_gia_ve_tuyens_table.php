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
        Schema::create('gia_ve_tuyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tuyen_xe_id')
                ->constrained('tuyen_xes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('loai_gia_ve', 100);
            $table->decimal('so_tien', 12, 2);
            $table->string('don_vi_tien_te', 10)->default('VND');
            $table->string('ghi_chu', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gia_ve_tuyens');
    }
};
