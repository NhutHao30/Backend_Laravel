<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nhanvien', function (Blueprint $table) {
            $table->string('TRANGTHAI')->default('Đang làm việc');
        });
    }

    public function down(): void
    {
        Schema::table('nhanvien', function (Blueprint $table) {
            $table->dropColumn('TRANGTHAI');
        });
    }
};
