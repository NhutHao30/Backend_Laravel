<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tinnhan', function (Blueprint $table) {
            $table->id('MATINNHAN');
            $table->unsignedBigInteger('MACUOCTROCHUYEN');
            $table->string('NGUOIGUI_ID', 100);
            $table->string('LOAINGUOIGUI', 50); // 'KHACHHANG' or 'NHANVIEN'
            $table->text('NOIDUNG')->nullable();
            $table->boolean('DADOC')->default(0);
            $table->timestamps();

            $table->foreign('MACUOCTROCHUYEN')->references('MACUOCTROCHUYEN')->on('cuoctrochuyen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tinnhan');
    }
};
