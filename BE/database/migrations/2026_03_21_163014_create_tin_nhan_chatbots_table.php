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
        Schema::create('tin_nhan_chatbots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoi_thoai_chatbot_id')
                ->constrained('hoi_thoai_chatbots')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->enum('vai_tro', ['client', 'bot', 'system']);
            $table->longText('noi_dung');
            $table->string('y_dinh_du_doan', 100)->nullable();
            $table->decimal('do_tin_cay', 5, 2)->nullable();
            $table->dateTime('thoi_diem_gui');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['hoi_thoai_chatbot_id', 'thoi_diem_gui'], 'idx_hoi_thoai_thoi_diem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tin_nhan_chatbots');
    }
};
