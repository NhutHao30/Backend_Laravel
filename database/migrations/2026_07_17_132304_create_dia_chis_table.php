<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diachi', function (Blueprint $table) {
            $table->id();
            $table->string('MAKH', 100);
            $table->string('DIACHI', 255)->nullable();
            $table->boolean('MACDINH')->default(0);
            $table->timestamps();

            $table->foreign('MAKH')->references('MAKH')->on('khachhang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diachi');
    }
};
