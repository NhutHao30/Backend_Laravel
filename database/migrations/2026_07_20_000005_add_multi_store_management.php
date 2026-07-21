<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMultiStoreManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tạo bảng cuahang
        if (!Schema::hasTable('cuahang')) {
            Schema::create('cuahang', function (Blueprint $table) {
                $table->id('MACUAHANG');
                $table->string('TENCUAHANG', 100);
                $table->string('DIACHI', 255)->nullable();
                $table->string('SDT', 20)->nullable();
                $table->string('TRANGTHAI', 50)->default('Đang hoạt động');
                $table->timestamps();
            });
        }

        // 2. Thêm MACUAHANG vào bảng nhanvien
        if (!Schema::hasColumn('nhanvien', 'MACUAHANG')) {
            Schema::table('nhanvien', function (Blueprint $table) {
                $table->unsignedBigInteger('MACUAHANG')->nullable()->after('USERNAME');
            });
        }

        // 3. Thêm MACUAHANG vào bảng hdban
        if (!Schema::hasColumn('hdban', 'MACUAHANG')) {
            Schema::table('hdban', function (Blueprint $table) {
                $table->unsignedBigInteger('MACUAHANG')->nullable()->after('MAHD');
            });
        }

        // 4. Thêm cửa hàng mặc định
        $defaultStore = DB::table('cuahang')->where('MACUAHANG', 1)->first();
        if (!$defaultStore) {
            DB::table('cuahang')->insert([
                'MACUAHANG' => 1,
                'TENCUAHANG' => 'Dola Bakery - Chi nhánh Trung tâm',
                'DIACHI' => 'Quy Nhơn, Bình Định',
                'SDT' => '0987654321',
                'TRANGTHAI' => 'Đang hoạt động',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Gán tất cả nhân sự và hóa đơn hiện tại cho chi nhánh 1
        DB::table('nhanvien')->whereNull('MACUAHANG')->update(['MACUAHANG' => 1]);
        DB::table('hdban')->whereNull('MACUAHANG')->update(['MACUAHANG' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('hdban', 'MACUAHANG')) {
            Schema::table('hdban', function (Blueprint $table) {
                $table->dropColumn('MACUAHANG');
            });
        }

        if (Schema::hasColumn('nhanvien', 'MACUAHANG')) {
            Schema::table('nhanvien', function (Blueprint $table) {
                $table->dropColumn('MACUAHANG');
            });
        }

        Schema::dropIfExists('cuahang');
    }
}
