<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nhanvien', function (Blueprint $table) {
            $table->string('USERNAME', 100)->primary();
            $table->string('HOTEN', 100);
            $table->date('NGAYSINH')->nullable();
            $table->string('GioiTinh', 5)->nullable();
            $table->string('DIACHI', 100)->nullable();
            $table->string('SDT', 15)->nullable();
            $table->string('CHUCVU', 50)->nullable();
            $table->decimal('LUONG', 18, 2)->default(0);
            $table->timestamps();

            $table->foreign('USERNAME')->references('USERNAME')->on('taikhoan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nhanvien');
    }
};
