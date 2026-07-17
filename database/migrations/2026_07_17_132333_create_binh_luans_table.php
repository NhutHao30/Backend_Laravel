<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('binhluan', function (Blueprint $table) {
            $table->id('MABL');
            $table->string('MAKH', 100);
            $table->string('MATINTUC', 10);
            $table->date('NGAYDANG')->useCurrent();
            $table->string('NOIDUNG', 500)->nullable();
            $table->timestamps();

            $table->foreign('MAKH')->references('MAKH')->on('khachhang');
            $table->foreign('MATINTUC')->references('MATINTUC')->on('tintuc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('binhluan');
    }
};
