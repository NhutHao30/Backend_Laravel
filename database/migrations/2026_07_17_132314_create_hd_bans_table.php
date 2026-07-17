<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hdban', function (Blueprint $table) {
            $table->string('MAHD', 10)->primary();
            $table->date('NGAYLAP')->useCurrent();
            $table->string('MAVANDON', 50)->nullable();
            $table->string('DonViVanChuyen', 50)->nullable();
            $table->string('GHICHU', 255)->nullable();
            $table->string('MAKH', 100);
            $table->decimal('TONGTIEN', 18, 2)->default(0);
            
            // MoMo payment fields
            $table->string('PHUONGTHUCTHANHTOAN', 50)->default('COD');
            $table->string('TRANGTHAITHANHTOAN', 50)->default('CHUA_THANH_TOAN');
            $table->string('MAGIAODICH_MOMO', 100)->nullable();
            
            $table->timestamps();

            $table->foreign('MAKH')->references('MAKH')->on('khachhang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hdban');
    }
};
