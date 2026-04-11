<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chuc_nangs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_chuc_nang', 150)->unique();
            $table->string('ten_chuc_nang', 150);
            $table->string('route_name', 200)->unique();
            $table->string('uri', 255);
            $table->string('http_method', 50);
            $table->string('nhom', 120)->nullable();
            $table->string('mo_ta', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chuc_nangs');
    }
};