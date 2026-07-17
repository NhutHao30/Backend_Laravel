<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thongtindh', function (Blueprint $table) {
            $table->string('MADH', 10)->primary();
            $table->date('NGAYDAT')->nullable();
            $table->date('NGAYGIAO')->nullable();
            $table->string('TRANGTHAI', 200)->nullable();
            $table->string('DIACHI', 255)->nullable();
            $table->string('MAHD', 10);
            $table->timestamps();

            $table->foreign('MAHD')->references('MAHD')->on('hdban');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thongtindh');
    }
};
