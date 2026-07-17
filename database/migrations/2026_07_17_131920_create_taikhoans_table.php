<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taikhoan', function (Blueprint $table) {
            $table->string('USERNAME', 100)->primary();
            $table->string('PASSWORD', 100);
            $table->integer('MAROLE');
            $table->string('EMAIL', 50)->unique();
            $table->string('OTP_CODE', 10)->nullable();
            $table->dateTime('OTP_EXPIRES_AT')->nullable();
            $table->string('DEVICE_TOKEN', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('MAROLE')->references('MAROLE')->on('vaitro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taikhoan');
    }
};
