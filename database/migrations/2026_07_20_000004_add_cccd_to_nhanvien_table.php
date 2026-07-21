<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCccdToNhanvienTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nhanvien', function (Blueprint $table) {
            $table->string('CCCD', 20)->nullable()->after('USERNAME');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nhanvien', function (Blueprint $table) {
            $table->dropColumn('CCCD');
        });
    }
}
