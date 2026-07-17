<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctiettintuc', function (Blueprint $table) {
            $table->id();
            $table->string('MATINTUC', 10);
            $table->string('BOCUC', 200)->nullable();
            $table->text('ARTICLE')->nullable();
            $table->string('HINHANH', 255)->nullable();
            $table->timestamps();

            $table->foreign('MATINTUC')->references('MATINTUC')->on('tintuc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctiettintuc');
    }
};
