<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\VaiTro;
use App\Models\TaiKhoan;
use App\Models\NhanVien;
use App\Models\LoaiSanPham;
use App\Models\SanPham;
use App\Models\GiamGia;
use App\Models\TinTuc;
use App\Models\ChiTietTinTuc;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Vai Trò
        VaiTro::insert([
            ['MAROLE' => 0, 'MOTA' => 'quanly', 'created_at' => $now, 'updated_at' => $now],
            ['MAROLE' => 1, 'MOTA' => 'nhanvien', 'created_at' => $now, 'updated_at' => $now],
            ['MAROLE' => 2, 'MOTA' => 'khachhang', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 2. Tài khoản Admin mẫu
        TaiKhoan::create([
            'USERNAME' => 'admin',
            'PASSWORD' => '123456',
            'MAROLE' => 0,
            'EMAIL' => 'admin@dolabakery.com',
        ]);
        NhanVien::create([
            'USERNAME' => 'admin',
            'HOTEN' => 'Quản Trị Viên',
            'CHUCVU' => 'Quản lý',
            'LUONG' => 15000000,
        ]);

        // 3. Loại Sản Phẩm
        LoaiSanPham::insert([
            ['MALOAI' => 'LSP001', 'TENLOAI' => 'BÁNH KEM', 'created_at' => $now, 'updated_at' => $now],
            ['MALOAI' => 'LSP002', 'TENLOAI' => 'BÁNH NGỌT', 'created_at' => $now, 'updated_at' => $now],
            ['MALOAI' => 'LSP003', 'TENLOAI' => 'BÁNH MÌ', 'created_at' => $now, 'updated_at' => $now],
            ['MALOAI' => 'LSP004', 'TENLOAI' => 'BÁNH TRÁNG MIỆNG', 'created_at' => $now, 'updated_at' => $now],
            ['MALOAI' => 'LSP005', 'TENLOAI' => 'BÁNH KHÔ', 'created_at' => $now, 'updated_at' => $now],
            ['MALOAI' => 'LSP006', 'TENLOAI' => 'BÁNH ĐÔNG LẠNH', 'created_at' => $now, 'updated_at' => $now],
            ['MALOAI' => 'LSP007', 'TENLOAI' => 'BÁNH THEO MÙA', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 4. Sản Phẩm
        $products = [
            ['SP001','BÁNH CUỘN VANI','2025-11-01','2025-11-11','CÁI',35000,20,'Thông tin sản phẩm đang cập nhật','productnew4.webp','LSP001'],
            ['SP002','BÁNH DONUT 45G','2025-10-15','2025-11-05','CÁI',20000,50,'Bột mỳ, sô cô la trắng, bột trà xanh','productnew7.webp','LSP005'],
            ['SP003','BÁNH DONUT DÂU 45G','2025-11-01','2025-11-08','CÁI',15000,30,'Bột mỳ, sô cô la trắng','productSale_8.webp','LSP005'],
            ['SP004','BÁNH HOÀNG KIM','2025-11-02','2025-11-06','CÁI',40000,20,'Thành phần: Bánh kem tươi cốt bánh 3 lớp vani','productSale_10.webp','LSP002'],
            ['SP005','BÁNH MOUSSE CHOCOLATE','2025-11-03','2025-11-20','CÁI',270000,5,'Bánh mousse socola, cốt bánh 1 lớp chiffon sô cô la chip','productMenu_6.webp','LSP001'],
            ['SP006','BÁNH KEM PRINCESS','2025-11-02','2025-11-06','CÁI',40000,20,'Bánh kem tươi cốt bánh 4 lớp chiffon trà bá tước','productMenu_8.webp','LSP001'],
            ['SP007','MOUSSE','2025-11-02','2025-11-06','CÁI',31000,20,'Thành phần: Kem sữa, kem thực vật, sữa tươi, đường','productMenu-14.webp','LSP007'],
            ['SP008','BÁNH PANNA COTTA','2025-11-02','2025-11-06','CÁI',22000,20,'Thành phần: Sữa tươi, kem sữa, đường, mứt phúc bồn tử.','productMenu-13.webp','LSP002'],
            ['SP009','BÁNH MÌ NƯỚNG PHÔ MAI QUE','2025-11-02','2025-11-06','CÁI',15000,20,'Thành phần: Bột mì, nước, phô mai bột','productMenu_9.webp','LSP003'],
            ['SP010','BÁNH QUY DỪA','2025-11-02','2025-11-06','CÁI',42000,20,'Thành phần: Bột mì, trứng gà, bơ, đường, bột dừa','productnew2.webp','LSP005'],
            ['SP011','MOUSSE CHANH LEO','2025-11-02','2025-11-06','CÁI',31000,20,'Thành phần: Kem sữa, kem thực vật, chanh leo','productSale_5.webp','LSP002'],
            ['SP012','CARAMEN','2025-11-02','2025-11-06','CÁI',13000,20,'Thông tin sản phẩm đang được cập nhật.','productSale_4.webp','LSP002'],
            ['SP013','BÁNH OPERA 90G','2025-11-02','2025-11-06','CÁI',40000,20,'Thành phần: Bột mỳ, đường, trứng, cà phê','productSale_6.webp','LSP007'],
            ['SP014','BÁNH QUY BƠ MỨT DÂU','2025-11-02','2025-11-06','CÁI',42000,20,'Thành phần: Bột mỳ, bơ, đường, trứng, mứt dâu.','productSale_3.webp','LSP005'],
            ['SP015','BÁNH MÌ NƯỚNG CARAMEN','2025-11-02','2025-11-06','CÁI',15000,20,'Thành phần: Bột mì, nước, sữa tươi, bơ, đường caramen','productSale_2.webp','LSP003'],
            ['SP016','BÁNH SỪNG BÒ MINI','2025-11-02','2025-11-10','CÁI',40000,20,'Thành phần: Bột mỳ, bơ, đường, sữa tươi, vừng trắng','productSale_1.webp','LSP005'],
            ['SP017','BÁNH GATO SOCOLA SỮA','2025-11-02','2025-11-06','CÁI',50000,20,'Thành phần: Trứng gà, bột mỳ, sô cô la, bơ','productSale_7.webp','LSP005'],
            ['SP018','BÁNH RED VELVET 90G','2025-11-02','2025-11-06','CÁI',58000,20,'Thành phần: Bơ, đường, trứng, bột mì, cacao, phô mai kem','productBestSale-2.webp','LSP007'],
            ['SP019','BÁNH SU KEM NHÂN SOCOLA','2025-11-02','2025-11-06','CÁI',30000,20,'Thành phần: Trứng gà, bột mỳ, bơ, nước, sô cô la','productBestSale-3.webp','LSP002'],
            ['SP020','BÁNH CUỘN','2025-11-02','2025-11-06','CÁI',39000,20,'Thành phần: Trứng gà, đường, bột mỳ, bột ca cao','productMenu-15.webp','LSP007'],
            ['SP021','BÁNH MÌ NƯỚNG BƠ TỎI','2025-11-02','2025-11-06','CÁI',15000,20,'Thông tin sản phẩm đang cập nhật','productMenu_10.webp','LSP003'],
            ['SP022','BÁNH QUY HẠNH NHÂN','2025-11-02','2025-11-06','CÁI',42000,20,'Thông tin sản phẩm đang cập nhật','product-Menu-11.webp','LSP002'],
            ['SP023','BÁNH LADY FINGER','2025-11-02','2025-11-06','CÁI',42000,20,'Thông tin sản phẩm đang cập nhật','productMenu-12.webp','LSP002'],
            ['SP024','BÁNH SU KEM NHÂN VANI','2025-11-02','2025-11-06','CÁI',29000,20,'Thông tin sản phẩm đang cập nhật','productnew3.webp','LSP002'],
        ];

        $sanphamData = [];
        foreach ($products as $p) {
            $sanphamData[] = [
                'MASP' => $p[0],
                'TENSP' => $p[1],
                'NSX' => $p[2],
                'HSD' => $p[3],
                'DVT' => $p[4],
                'GIABAN' => $p[5],
                'SOLUONG' => $p[6],
                'GHICHU' => $p[7],
                'HINHANH' => $p[8],
                'MALOAI' => $p[9],
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        SanPham::insert($sanphamData);

        // 5. Giảm Giá
        GiamGia::insert([
            ['MASP' => 'SP002', 'GIAM' => 10, 'THOIGIANGIAM' => '2026-11-30', 'created_at' => $now, 'updated_at' => $now],
            ['MASP' => 'SP003', 'GIAM' => 0, 'THOIGIANGIAM' => '2026-11-25', 'created_at' => $now, 'updated_at' => $now],
            ['MASP' => 'SP005', 'GIAM' => 20, 'THOIGIANGIAM' => '2026-12-31', 'created_at' => $now, 'updated_at' => $now],
            ['MASP' => 'SP010', 'GIAM' => 5, 'THOIGIANGIAM' => '2026-11-15', 'created_at' => $now, 'updated_at' => $now],
            ['MASP' => 'SP013', 'GIAM' => 0, 'THOIGIANGIAM' => '2026-01-01', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 6. Tin Tức
        TinTuc::insert([
            ['MATINTUC' => 'TT01', 'HINHANH' => 'post_img-1.webp', 'NGAYDANG' => '2026-01-01', 'TIEUDE' => 'Donut chỉ từ 8k tại Dola', 'MOTA' => 'Nhắc đến bánh Donut dân sành thưởng thức hẳn không còn xa lạ gì với món ăn vặt rất phổ biến ở các nước phương Tây này. Dù có nguồn...', 'created_at' => $now, 'updated_at' => $now],
            ['MATINTUC' => 'TT02', 'HINHANH' => 'post_img-2.webp', 'NGAYDANG' => '2026-01-01', 'TIEUDE' => 'Croissant ngàn lớp - đa dạng cách...', 'MOTA' => 'Những chiếc bánh sừng bò với hương bơ thơm béo đặc trưng lại còn đưa miệng với độ giòn xốp, dai dai từ "ngàn" lớp bánh. Nổi bật với hình...', 'created_at' => $now, 'updated_at' => $now],
            ['MATINTUC' => 'TT03', 'HINHANH' => 'post_img-3.webp', 'NGAYDANG' => '2026-01-01', 'TIEUDE' => 'Bánh Tart thơm ngậy không thể bỏ...', 'MOTA' => 'Tart trứng là loại bánh đường phố nổi tiếng ở Hong Kong được rất nhiều người yêu thích. Không những thế, trong bảng xếp hạng 50 loại món ăn ngon...', 'created_at' => $now, 'updated_at' => $now],
            ['MATINTUC' => 'TT04', 'HINHANH' => 'post_img-4.webp', 'NGAYDANG' => '2026-01-01', 'TIEUDE' => 'Bánh đông lạnh tiện lợi - ngon...', 'MOTA' => 'Bánh đông lạnh đã dần trở thành một sản phẩm quen thuộc cho các Mẹ Đảm sau một thời gian dài giãn cách. Sở dĩ, bánh đông lạnh được nhiều...', 'created_at' => $now, 'updated_at' => $now],
            ['MATINTUC' => 'TT05', 'HINHANH' => 'post_img-5.webp', 'NGAYDANG' => '2026-01-01', 'TIEUDE' => 'Bánh ngọt - Các loại bánh ngọt được...', 'MOTA' => 'Đối với những người có niềm đam mê với đồ ngọt thì chắc chắn bánh ngọt đã trở thành một phần không thể thiếu. Những chiếc bánh ngọt hớp hồn...', 'created_at' => $now, 'updated_at' => $now],
            ['MATINTUC' => 'TT06', 'HINHANH' => 'post_img-6.webp', 'NGAYDANG' => '2026-01-01', 'TIEUDE' => 'Khám phá menu bánh quy khô thơm...', 'MOTA' => 'Bánh quy khô là món ăn thơm ngon, bổ dưỡng, được nhiều người tiêu dùng ưa thích lựa chọn. Không giống những loại bánh khác, bánh quy đặc biệt với hương...', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 7. Chi Tiết Tin Tức
        ChiTietTinTuc::insert([
            ['MATINTUC' => 'TT01', 'BOCUC' => 'Nguồn gốc của chiếc bánh vòng', 'ARTICLE' => 'Donut là loại bánh ngọt rán hoặc nướng dùng như món tráng miệng hay món ăn vặt.', 'HINHANH' => 'post_img-1.webp', 'created_at' => $now, 'updated_at' => $now],
            ['MATINTUC' => 'TT01', 'BOCUC' => 'Hương vị đa dạng tại Dola', 'ARTICLE' => 'Tại Dola Bakery, chúng tôi biến tấu Donut với hàng chục loại topping khác nhau.', 'HINHANH' => null, 'created_at' => $now, 'updated_at' => $now],
            ['MATINTUC' => 'TT01', 'BOCUC' => 'Ưu đãi đặc biệt', 'ARTICLE' => 'Chỉ với 8.000đ, bạn đã có thể sở hữu một chiếc Donut thơm ngon.', 'HINHANH' => null, 'created_at' => $now, 'updated_at' => $now],
            ['MATINTUC' => 'TT02', 'BOCUC' => 'Kết cấu ngàn lớp độc đáo', 'ARTICLE' => 'Bánh sừng bò (Croissant) là niềm tự hào của ẩm thực Pháp.', 'HINHANH' => 'post_img-2.webp', 'created_at' => $now, 'updated_at' => $now],
            ['MATINTUC' => 'TT03', 'BOCUC' => 'Món quà từ Hong Kong', 'ARTICLE' => 'Tart trứng Hong Kong sở hữu lớp vỏ bánh quy giòn tan.', 'HINHANH' => 'post_img-3.webp', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
