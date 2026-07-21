<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMacuahangToKhachhang extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('khachhang', 'MACUAHANG')) {
            Schema::table('khachhang', function (Blueprint $table) {
                $table->unsignedBigInteger('MACUAHANG')->nullable()->after('USERNAME');
            });
            
            // Gán những khách cũ cho chi nhánh 1
            DB::table('khachhang')->update(['MACUAHANG' => 1]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('khachhang', 'MACUAHANG')) {
            Schema::table('khachhang', function (Blueprint $table) {
                $table->dropColumn('MACUAHANG');
            });
        }
    }
}
