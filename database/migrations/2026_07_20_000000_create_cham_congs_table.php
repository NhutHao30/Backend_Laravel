<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamcong', function (Blueprint $table) {
            $table->id();
            $table->string('USERNAME', 50);
            $table->date('NGAYCHAMCONG');
            $table->boolean('TRANGTHAI')->default(1); // 1: Có mặt, 0: Vắng mặt
            $table->timestamps();

            $table->foreign('USERNAME')->references('USERNAME')->on('nhanvien')->onDelete('cascade');
            
            // Một nhân viên chỉ có 1 bản ghi chấm công mỗi ngày
            $table->unique(['USERNAME', 'NGAYCHAMCONG']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamcong');
    }
};
