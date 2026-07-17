<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chitiethdban', function (Blueprint $table) {
            $table->string('MAHD', 10);
            $table->string('MASP', 10);
            $table->integer('SOLUONG')->default(0);
            $table->decimal('DONGIA', 18, 2)->default(0);
            $table->decimal('THANHTIEN', 18, 2)->virtualAs('SOLUONG * DONGIA');
            $table->timestamps();

            $table->primary(['MAHD', 'MASP']);
            $table->foreign('MAHD')->references('MAHD')->on('hdban');
            $table->foreign('MASP')->references('MASP')->on('sanpham');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chitiethdban');
    }
};
