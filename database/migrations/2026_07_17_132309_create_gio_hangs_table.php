<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giohang', function (Blueprint $table) {
            $table->string('MAGIOHANG', 10)->primary();
            $table->string('MAKH', 100);
            $table->date('NGAYTAO')->useCurrent();
            $table->timestamps();

            $table->foreign('MAKH')->references('MAKH')->on('khachhang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giohang');
    }
};
