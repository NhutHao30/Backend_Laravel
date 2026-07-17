<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chitiethdnhap', function (Blueprint $table) {
            $table->string('MAHDNHAP', 10);
            $table->string('MASP', 10);
            $table->integer('SOLUONGTCT')->default(0);
            $table->integer('SOLUONGTN')->default(0);
            $table->decimal('DONGIANHAP', 18, 2)->default(0);
            $table->decimal('THANHTIENN', 18, 2)->virtualAs('SOLUONGTN * DONGIANHAP');
            $table->string('GHICHU', 255)->nullable();
            $table->timestamps();

            $table->primary(['MAHDNHAP', 'MASP']);
            $table->foreign('MAHDNHAP')->references('MAHDNHAP')->on('hdnhap');
            $table->foreign('MASP')->references('MASP')->on('sanpham');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chitiethdnhap');
    }
};
