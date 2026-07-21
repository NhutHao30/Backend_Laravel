<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\KhachHang;
use App\Models\Sanpham;
use App\Models\CuaHang;

class CskhController extends Controller
{
    /**
     * Tra cứu lịch sử mua hàng, địa chỉ, hạng thành viên của khách hàng
     */
    public function getCustomerInfo($makh)
    {
        $khachHang = KhachHang::with(['taikhoan'])->where('MAKH', $makh)->first();
        if (!$khachHang) {
            return response()->json(['error' => 'Không tìm thấy khách hàng'], 404);
        }

        $hoaDon = DB::table('hdban')
            ->where('MAKH', $makh)
            ->orderBy('NGAYLAP', 'desc')
            ->get();

        return response()->json([
            'khachhang' => $khachHang,
            'hoadon' => $hoaDon,
            'diemtichluy' => $khachHang->DIEMTICHLUY
        ]);
    }

    /**
     * Tra cứu tồn kho của một sản phẩm trên tất cả các chi nhánh
     */
    public function checkProductStock(Request $request)
    {
        $masp = $request->query('masp');
        if (!$masp) return response()->json([]);

        $stockInfo = DB::table('tonkho_cuahang')
            ->join('cuahang', 'tonkho_cuahang.MACUAHANG', '=', 'cuahang.MACUAHANG')
            ->join('sanpham', 'tonkho_cuahang.MASP', '=', 'sanpham.MASP')
            ->where('tonkho_cuahang.MASP', $masp)
            ->select('cuahang.TENCUAHANG', 'cuahang.DIACHI', 'tonkho_cuahang.SOLUONG_TON', 'sanpham.TENSP')
            ->orderBy('tonkho_cuahang.SOLUONG_TON', 'desc')
            ->get();

        return response()->json($stockInfo);
    }

    /**
     * Lọc danh sách cửa hàng gần khách hàng (theo Tỉnh/Thành phố hoặc Quận)
     */
    public function findNearbyStores(Request $request)
    {
        $addressKeyword = $request->query('keyword'); // Ví dụ: 'Tân Phú' hoặc 'Hồ Chí Minh'
        
        $query = CuaHang::query();
        if ($addressKeyword) {
            $query->where('DIACHI', 'LIKE', '%' . $addressKeyword . '%');
        }

        return response()->json($query->get());
    }

    /**
     * Đặt hàng giùm khách (Hỗ trợ nhiều sản phẩm)
     */
    public function placeOrderForCustomer(Request $request, $makh)
    {
        $request->validate([
            'MACUAHANG' => 'required',
            'items' => 'required|array|min:1',
            'items.*.MASP' => 'required',
            'items.*.SOLUONG' => 'required|numeric|min:1'
        ]);

        $khachHang = KhachHang::with(['taikhoan'])->where('MAKH', $makh)->first();
        if (!$khachHang) return response()->json(['error' => 'Khách hàng không tồn tại'], 404);

        DB::beginTransaction();
        try {
            $tongTien = 0;
            $mahd = 'HD' . substr(time(), -8);
            $chiTietData = [];
            
            // Xử lý từng sản phẩm
            foreach ($request->items as $itemReq) {
                $sp = Sanpham::find($itemReq['MASP']);
                if (!$sp) {
                    throw new \Exception('Một trong các sản phẩm không tồn tại');
                }

                // Kiểm tra và trừ tồn kho
                $tonKho = DB::table('tonkho_cuahang')
                    ->where('MACUAHANG', $request->MACUAHANG)
                    ->where('MASP', $itemReq['MASP'])
                    ->lockForUpdate()
                    ->first();

                if (!$tonKho || $tonKho->SOLUONG_TON < $itemReq['SOLUONG']) {
                    throw new \Exception('Cửa hàng không đủ số lượng tồn kho cho sản phẩm: ' . $sp->TENSP);
                }

                DB::table('tonkho_cuahang')
                    ->where('MACUAHANG', $request->MACUAHANG)
                    ->where('MASP', $itemReq['MASP'])
                    ->update(['SOLUONG_TON' => $tonKho->SOLUONG_TON - $itemReq['SOLUONG']]);

                // Trừ bảng sanpham (nếu cần)
                $sp->SOLUONG -= $itemReq['SOLUONG'];
                $sp->save();

                $tongTien += $sp->GIABAN * $itemReq['SOLUONG'];

                // Lưu dữ liệu chi tiết chờ insert
                $chiTietData[] = [
                    'MAHD' => $mahd,
                    'MASP' => $sp->MASP,
                    'SOLUONG' => $itemReq['SOLUONG'],
                    'DONGIA' => $sp->GIABAN
                ];
            }

            // Tạo hóa đơn TRƯỚC
            $ghiChu = 'Đơn hàng do CSKH đặt giùm.';
            if ($request->has('DIACHIGIAO') && $request->DIACHIGIAO) {
                $ghiChu .= ' Giao tới: ' . $request->DIACHIGIAO;
            }

            \App\Models\HdBan::create([
                'MAHD' => $mahd,
                'NGAYLAP' => now(),
                'TONGTIEN' => $tongTien + ($request->shippingFee ?? 0),
                'PHUONGTHUCTHANHTOAN' => 'COD (Giao hàng)',
                'TRANGTHAITHANHTOAN' => 'Chờ xử lý',
                'GHICHU' => $ghiChu,
                'MAKH' => $makh,
                'MACUAHANG' => $request->MACUAHANG,
                'DonViVanChuyen' => 'Giao Hàng Nhanh'
            ]);

            // Tạo chi tiết SAU (để không bị lỗi Foreign Key)
            foreach ($chiTietData as $ct) {
                \App\Models\ChiTietHdBan::create($ct);
            }

            DB::commit();

            // Gửi email
            $email = $khachHang->taikhoan ? $khachHang->taikhoan->EMAIL : null;
            if ($email) {
                \App\Jobs\SendOrderEmailJob::dispatch($email, $mahd, $tongTien, $khachHang->HOTEN);
            }

            return response()->json(['message' => 'Đặt hàng thành công', 'mahd' => $mahd]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi đặt hàng: ' . $e->getMessage()], 500);
        }
    }
}
