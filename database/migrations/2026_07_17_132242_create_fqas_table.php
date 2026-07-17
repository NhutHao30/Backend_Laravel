<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fqa', function (Blueprint $table) {
            $table->id();
            $table->string('HOTEN', 50)->nullable();
            $table->string('EMAIL', 50)->nullable();
            $table->string('SDT', 15)->nullable();
            $table->string('CONTENT', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fqa');
    }
};
