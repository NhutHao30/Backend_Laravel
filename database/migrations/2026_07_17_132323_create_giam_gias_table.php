<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giamgia', function (Blueprint $table) {
            $table->string('MASP', 10)->primary();
            $table->integer('GIAM')->default(0);
            $table->date('THOIGIANGIAM')->nullable();
            $table->timestamps();

            $table->foreign('MASP')->references('MASP')->on('sanpham');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giamgia');
    }
};
