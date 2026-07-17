<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chitietgiohang', function (Blueprint $table) {
            $table->string('MAGIOHANG', 10);
            $table->string('MASP', 10);
            $table->integer('SOLUONG')->default(0);
            $table->decimal('DonGia', 18, 2)->default(0);
            $table->decimal('ThanhTien', 18, 2)->virtualAs('SOLUONG * DonGia');
            $table->string('GHICHU', 255)->nullable();
            $table->timestamps();

            $table->primary(['MAGIOHANG', 'MASP']);
            $table->foreign('MAGIOHANG')->references('MAGIOHANG')->on('giohang');
            $table->foreign('MASP')->references('MASP')->on('sanpham');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chitietgiohang');
    }
};
