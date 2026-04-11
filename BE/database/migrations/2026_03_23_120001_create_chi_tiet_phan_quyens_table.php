<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_phan_quyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phan_quyen_quan_tri_vien_id')
                ->constrained('phan_quyen_quan_tri_viens')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('chuc_nang_id')
                ->constrained('chuc_nangs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->unique(['phan_quyen_quan_tri_vien_id', 'chuc_nang_id'], 'uq_chi_tiet_phan_quyen');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_phan_quyens');
    }
};