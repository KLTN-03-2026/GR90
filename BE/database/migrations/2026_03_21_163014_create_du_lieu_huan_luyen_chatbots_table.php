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
        Schema::create('du_lieu_huan_luyen_chatbots', function (Blueprint $table) {
            $table->id();
            $table->text('cau_hoi');
            $table->string('y_dinh', 100);
            $table->json('thuc_the_json')->nullable();
            $table->text('cau_tra_loi_mau')->nullable();
            $table->string('nguon', 100)->nullable();
            $table->tinyInteger('da_duyet')->default(0);
            $table->foreignId('nguoi_duyet_id')
                ->nullable()
                ->constrained('quan_tri_viens')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['y_dinh', 'da_duyet'], 'idx_y_dinh_duyet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('du_lieu_huan_luyen_chatbots');
    }
};
