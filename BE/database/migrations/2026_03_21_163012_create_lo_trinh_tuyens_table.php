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
        Schema::create('lo_trinh_tuyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tuyen_xe_id')
                ->constrained('tuyen_xes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->enum('chieu', ['di', 've']);
            $table->longText('mo_ta_lo_trinh');
            $table->timestamps();

            $table->unique(['tuyen_xe_id', 'chieu'], 'uq_tuyen_chieu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lo_trinh_tuyens');
    }
};
