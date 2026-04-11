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
        Schema::create('tu_khoa_tuyen_xes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tuyen_xe_id')
                ->constrained('tuyen_xes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('tu_khoa', 255);
            $table->timestamps();

            $table->unique(['tuyen_xe_id', 'tu_khoa'], 'uq_tuyen_tu_khoa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tu_khoa_tuyen_xes');
    }
};
