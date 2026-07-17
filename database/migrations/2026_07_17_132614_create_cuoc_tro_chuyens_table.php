<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuoctrochuyen', function (Blueprint $table) {
            $table->id('MACUOCTROCHUYEN');
            $table->string('MAKH', 100);
            $table->string('USERNAME_NV', 100)->nullable();
            $table->string('TRANGTHAI', 50)->default('DANG_HOAT_DONG');
            $table->timestamps();

            $table->foreign('MAKH')->references('MAKH')->on('khachhang');
            $table->foreign('USERNAME_NV')->references('USERNAME')->on('nhanvien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuoctrochuyen');
    }
};
