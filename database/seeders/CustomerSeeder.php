<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TaiKhoan;
use App\Models\KhachHang;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Vô hiệu hóa kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Danh sách tài khoản khách hàng mẫu
        $customers = [
            'khachhang_01',
            'customer1',
            'khachhang_02'
        ];

        foreach ($customers as $index => $username) {
            // Xóa cũ nếu có để tránh lỗi
            KhachHang::where('USERNAME', $username)->delete();
            TaiKhoan::where('USERNAME', $username)->delete();

            // 1. Tạo tài khoản
            $tk = TaiKhoan::create([
                'USERNAME' => $username,
                'PASSWORD' => '123456', // Sẽ được tự động hash nhờ cast 'hashed'
                'MAROLE'   => 2,
                'EMAIL'    => $username . '@gmail.com',
            ]);

            // 2. Tạo thông tin khách hàng
            KhachHang::create([
                'MAKH'     => 'KH_SEED_' . ($index + 1),
                'HOTEN'    => 'Khách Hàng ' . ($index + 1),
                'SDT'      => '098765432' . $index,
                'USERNAME' => $tk->USERNAME,
                'DIEMTICHLUY' => 500,
            ]);
        }

        // Bật lại kiểm tra khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
