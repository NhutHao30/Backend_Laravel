<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khachhang', function (Blueprint $table) {
            $table->string('MAKH', 100)->primary();
            $table->string('HOTEN', 50)->nullable();
            $table->date('NGAYSINH')->nullable();
            $table->string('SDT', 15)->nullable();
            $table->string('GioiTinh', 5)->nullable();
            $table->string('DIACHI', 255)->nullable();
            $table->integer('DIEMTICHLUY')->default(0);
            $table->string('USERNAME', 100)->nullable()->unique();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('USERNAME')->references('USERNAME')->on('taikhoan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khachhang');
    }
};
