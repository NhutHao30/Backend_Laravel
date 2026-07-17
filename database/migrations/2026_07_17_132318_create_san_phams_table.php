<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanpham', function (Blueprint $table) {
            $table->string('MASP', 10)->primary();
            $table->string('TENSP', 100)->nullable();
            $table->date('NSX')->nullable();
            $table->date('HSD')->nullable();
            $table->string('DVT', 20)->nullable();
            $table->decimal('GIABAN', 18, 2)->default(0);
            $table->integer('SOLUONG')->default(0);
            $table->text('GHICHU')->nullable();
            $table->string('HINHANH', 255)->nullable();
            $table->string('MALOAI', 10);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('MALOAI')->references('MALOAI')->on('loaisanpham');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanpham');
    }
};
