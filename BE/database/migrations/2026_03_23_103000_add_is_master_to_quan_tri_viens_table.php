<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quan_tri_viens', function (Blueprint $table) {
            $table->tinyInteger('is_master')->default(0)->after('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::table('quan_tri_viens', function (Blueprint $table) {
            $table->dropColumn('is_master');
        });
    }
};
