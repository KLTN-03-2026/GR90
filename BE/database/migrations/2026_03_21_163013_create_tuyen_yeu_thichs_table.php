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
        Schema::create('tuyen_yeu_thichs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khach_hang_id')
                ->constrained('khach_hangs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('tuyen_xe_id')
                ->constrained('tuyen_xes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['khach_hang_id', 'tuyen_xe_id'], 'uq_kh_tuyen_yeu_thich');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuyen_yeu_thichs');
    }
};
