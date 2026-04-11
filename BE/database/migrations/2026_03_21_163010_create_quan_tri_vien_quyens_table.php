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
        Schema::create('quan_tri_vien_quyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quan_tri_vien_id')
                ->constrained('quan_tri_viens')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('phan_quyen_quan_tri_vien_id')
                ->constrained('phan_quyen_quan_tri_viens')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique(['quan_tri_vien_id', 'phan_quyen_quan_tri_vien_id'], 'uq_qtv_quyen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quan_tri_vien_quyens');
    }
};
