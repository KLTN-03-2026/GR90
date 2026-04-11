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
        Schema::create('luot_xem_tuyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khach_hang_id')
                ->nullable()
                ->constrained('khach_hangs')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('tuyen_xe_id')
                ->constrained('tuyen_xes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('dia_chi_ip', 45)->nullable();
            $table->string('thiet_bi', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('luot_xem_tuyens');
    }
};
