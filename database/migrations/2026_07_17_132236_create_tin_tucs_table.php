<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tintuc', function (Blueprint $table) {
            $table->string('MATINTUC', 10)->primary();
            $table->string('TIEUDE', 200)->nullable();
            $table->string('HINHANH', 255)->nullable();
            $table->date('NGAYDANG')->nullable();
            $table->string('MOTA', 1000)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tintuc');
    }
};
