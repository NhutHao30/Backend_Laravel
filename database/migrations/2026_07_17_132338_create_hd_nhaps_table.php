<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hdnhap', function (Blueprint $table) {
            $table->string('MAHDNHAP', 10)->primary();
            $table->dateTime('NGAYLAP')->useCurrent();
            $table->string('USERNAME', 100)->nullable();
            $table->string('GHICHU', 255)->nullable();
            $table->timestamps();

            $table->foreign('USERNAME')->references('USERNAME')->on('nhanvien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hdnhap');
    }
};
