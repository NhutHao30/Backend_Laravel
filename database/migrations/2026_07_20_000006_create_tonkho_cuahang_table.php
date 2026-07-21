<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTonkhoCuahangTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tonkho_cuahang')) {
            Schema::create('tonkho_cuahang', function (Blueprint $table) {
                $table->unsignedBigInteger('MACUAHANG');
                $table->string('MASP', 50);
                $table->integer('SOLUONG_TON')->default(0);
                $table->primary(['MACUAHANG', 'MASP']);
                $table->timestamps();
            });
        }

        // Chuyển toàn bộ số lượng tồn kho hiện tại của bảng sanpham 
        // vào cửa hàng trung tâm (MACUAHANG = 1)
        $sanphams = DB::table('sanpham')->get();
        foreach ($sanphams as $sp) {
            DB::table('tonkho_cuahang')->updateOrInsert(
                ['MACUAHANG' => 1, 'MASP' => $sp->MASP],
                ['SOLUONG_TON' => $sp->SOLUONG, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down()
    {
        Schema::dropIfExists('tonkho_cuahang');
    }
}
