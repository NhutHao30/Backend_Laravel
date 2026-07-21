<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GioHang;
use App\Models\ChiTietGioHang;
use App\Models\SanPham;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get or create the cart for the authenticated user.
     */
    private function getCart()
    {
        $user = Auth::user(); // TaiKhoan
        $khachHang = \App\Models\KhachHang::where('USERNAME', $user->USERNAME)->first();
        
        if (!$khachHang) {
            throw new \Exception("Vui lòng cập nhật thông tin khách hàng trước khi mua sắm.");
        }

        $cart = GioHang::firstOrCreate(
            ['MAKH' => $khachHang->MAKH],
            ['MAGIOHANG' => 'GH' . substr(time(), -8), 'NGAYTAO' => now()]
        );

        return $cart;
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ hàng
     */
    public function index()
    {
        try {
            $cart = $this->getCart();
            $items = ChiTietGioHang::with('sanpham')->where('MAGIOHANG', $cart->MAGIOHANG)->get();
            
            $formatted = $items->map(function ($item) {
                return [
                    'id' => $item->MASP,
                    'name' => $item->sanpham ? $item->sanpham->TENSP : 'Sản phẩm ' . $item->MASP,
                    'price' => $item->DonGia,
                    'quantity' => $item->SOLUONG,
                    'image' => $item->sanpham ? $item->sanpham->HINHANH : null,
                    'note' => $item->GHICHU
                ];
            });

            return response()->json(['cartId' => $cart->MAGIOHANG, 'items' => $formatted]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function add(Request $request)
    {
        try {
            $request->validate([
                'masp' => 'required|string',
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = $this->getCart();
            $sp = SanPham::find($request->masp);
            
            if (!$sp) return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
            if ($sp->SOLUONG < $request->quantity) {
                return response()->json(['error' => 'Kho không đủ số lượng'], 400);
            }

            // Check if item exists in cart
            $item = ChiTietGioHang::where('MAGIOHANG', $cart->MAGIOHANG)->where('MASP', $request->masp)->first();

            if ($item) {
                $item->SOLUONG += $request->quantity;
                if ($item->SOLUONG > $sp->SOLUONG) return response()->json(['error' => 'Vượt quá số lượng tồn kho'], 400);
                $item->save();
            } else {
                ChiTietGioHang::create([
                    'MAGIOHANG' => $cart->MAGIOHANG,
                    'MASP' => $sp->MASP,
                    'SOLUONG' => $request->quantity,
                    'DonGia' => $sp->GIABAN,
                    'GHICHU' => $request->note ?? null
                ]);
            }

            return response()->json(['message' => 'Đã thêm vào giỏ hàng']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật số lượng
     */
    public function updateQuantity(Request $request)
    {
        try {
            $request->validate([
                'masp' => 'required|string',
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = $this->getCart();
            $item = ChiTietGioHang::where('MAGIOHANG', $cart->MAGIOHANG)->where('MASP', $request->masp)->first();
            if (!$item) return response()->json(['error' => 'Không tìm thấy sản phẩm trong giỏ'], 404);

            $sp = SanPham::find($request->masp);
            if ($request->quantity > $sp->SOLUONG) {
                return response()->json(['error' => 'Vượt quá số lượng tồn kho'], 400);
            }

            $item->SOLUONG = $request->quantity;
            $item->save();

            return response()->json(['message' => 'Đã cập nhật giỏ hàng']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ
     */
    public function remove($masp)
    {
        try {
            $cart = $this->getCart();
            ChiTietGioHang::where('MAGIOHANG', $cart->MAGIOHANG)->where('MASP', $masp)->delete();
            return response()->json(['message' => 'Đã xóa khỏi giỏ hàng']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Thanh toán Online (Khách hàng đặt hàng)
     */
    public function checkoutOnline(Request $request)
    {
        try {
            $cart = $this->getCart();
            $items = ChiTietGioHang::where('MAGIOHANG', $cart->MAGIOHANG)->get();
            
            if ($items->isEmpty()) {
                return response()->json(['error' => 'Giỏ hàng trống'], 400);
            }

            $paymentMethod = $request->input('paymentMethod', 'COD');
            $address = $request->input('address', 'Giao tới địa chỉ mặc định');
            $note = $request->input('note', 'Khách hàng đặt online');
            
            $khachHang = \App\Models\KhachHang::find($cart->MAKH);
            if (!$khachHang) {
                return response()->json(['error' => 'Không tìm thấy thông tin khách hàng'], 404);
            }

            DB::beginTransaction();

            $total = 0;
            
            // Tìm cửa hàng có đủ hàng cho TẤT CẢ sản phẩm trong giỏ
            $cuahangs = \App\Models\CuaHang::where('TRANGTHAI', 'Đang hoạt động')->get();
            $eligibleStores = [];
            
            foreach ($cuahangs as $ch) {
                $isEnoughStock = true;
                foreach ($items as $item) {
                    $tonKho = \App\Models\TonKhoCuaHang::where('MACUAHANG', $ch->MACUAHANG)
                                ->where('MASP', $item->MASP)->first();
                    if (!$tonKho || $tonKho->SOLUONG_TON < $item->SOLUONG) {
                        $isEnoughStock = false;
                        break;
                    }
                }
                if ($isEnoughStock) {
                    $eligibleStores[] = $ch;
                }
            }

            if (empty($eligibleStores)) {
                throw new \Exception("Hiện tại không có cửa hàng nào còn đủ hàng cho toàn bộ giỏ hàng của bạn.");
            }

            $selectedStoreId = null;
            $minFee = -1;

            // GHN API Token
            $ghnToken = env('GHN_API_TOKEN');

            // Tính cước phí để chọn cửa hàng gần nhất (cước phí rẻ nhất = gần nhất)
            foreach ($eligibleStores as $ch) {
                $fee = 999999; // Default max fee
                $shopId = $ch->GHN_SHOP_ID ?? env('GHN_SHOP_ID'); // Fallback to default if not set
                
                if ($shopId) {
                    try {
                        $response = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders([
                            'Token' => $ghnToken,
                            'ShopId' => (int)$shopId
                        ])->post('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', [
                            'service_type_id' => 2,
                            'to_ward_code' => (string)$request->to_ward_code,
                            'to_district_id' => (int)$request->to_district_id,
                            'weight' => 1000,
                            'length' => 20,
                            'width' => 20,
                            'height' => 10
                        ]);
                        $feeData = $response->json();
                        if (isset($feeData['code']) && $feeData['code'] === 200 && isset($feeData['data']['total'])) {
                            $fee = $feeData['data']['total'];
                        }
                    } catch (\Exception $e) {
                        // Ignore error, keep fee at 999999
                    }
                }

                if ($minFee === -1 || $fee < $minFee) {
                    $minFee = $fee;
                    $selectedStoreId = $ch->MACUAHANG;
                }
            }

            if (!$selectedStoreId) {
                $selectedStoreId = $eligibleStores[0]->MACUAHANG; // Fallback
            }

            // Trừ tồn kho tại cửa hàng được chọn và tính tổng tiền
            foreach ($items as $item) {
                $tonKho = \App\Models\TonKhoCuaHang::where('MACUAHANG', $selectedStoreId)
                            ->where('MASP', $item->MASP)->lockForUpdate()->first();
                
                \Illuminate\Support\Facades\DB::table('tonkho_cuahang')
                    ->where('MACUAHANG', $selectedStoreId)
                    ->where('MASP', $item->MASP)
                    ->update(['SOLUONG_TON' => $tonKho->SOLUONG_TON - $item->SOLUONG]);
                
                // (Tùy chọn) Vẫn trừ bảng sanpham để tương thích ngược nếu Frontend đang đọc từ bảng này
                $sp = SanPham::find($item->MASP);
                if ($sp) {
                    $sp->SOLUONG -= $item->SOLUONG;
                    $sp->save();
                }

                $total += $item->SOLUONG * $item->DonGia;
            }

            $mahd = $request->mahd ?? ('HD' . substr(time(), -8));
            $mavandon = null;
            
            // Logic mới: Tất cả đơn hàng (COD hay Chuyển khoản) đều chờ Admin xác nhận
            // Tuyệt đối KHÔNG đẩy sang GHN ngay lập tức để tránh Shipper đến lấy hàng ảo.

            \App\Models\HdBan::create([
                'MAHD' => $mahd,
                'NGAYLAP' => now(),
                'TONGTIEN' => $total + ($request->shippingFee ?? 0),
                'PHUONGTHUCTHANHTOAN' => $paymentMethod === 'MOMO' ? 'Ví Momo P2P' : ($paymentMethod === 'VIETQR' ? 'Chuyển khoản VietQR' : 'COD (Giao hàng)'),
                'TRANGTHAITHANHTOAN' => $paymentMethod === 'COD' ? 'Chờ xử lý' : 'Đang xử lý (Chờ xác nhận CK)',
                'GHICHU' => "Đơn hàng Online. Giao tới: {$address}. Ghi chú: {$note} [GHN_WARD:{$request->to_ward_code},GHN_DIST:{$request->to_district_id}]",
                'MAKH' => $cart->MAKH,
                'MAVANDON' => $mavandon,
                'DonViVanChuyen' => 'Giao Hàng Nhanh',
                'MACUAHANG' => $selectedStoreId,
            ]);

            // Chuyển chi tiết giỏ hàng sang chi tiết hóa đơn
            foreach ($items as $item) {
                \App\Models\ChiTietHdBan::create([
                    'MAHD' => $mahd,
                    'MASP' => $item->MASP,
                    'SOLUONG' => $item->SOLUONG,
                    'DONGIA' => $item->DonGia,
                ]);
            }

            // Xóa giỏ hàng sau khi đặt thành công
            ChiTietGioHang::where('MAGIOHANG', $cart->MAGIOHANG)->delete();
            
            // Tích điểm cho khách hàng (Ví dụ: 10.000 VNĐ = 1 điểm)
            if ($khachHang) {
                $diemCong = floor($total / 10000);
                $khachHang->DIEMTICHLUY += $diemCong;
                $khachHang->save();
            }

            DB::commit();

            // Gửi email xác nhận qua RabbitMQ
            $email = $khachHang && $khachHang->taikhoan ? $khachHang->taikhoan->EMAIL : null;
            if ($email) {
                \App\Jobs\SendOrderEmailJob::dispatch($email, $mahd, $total + ($request->shippingFee ?? 0), $khachHang->HOTEN);
            }
            
            // Xóa cache vì tồn kho đã thay đổi
            \Illuminate\Support\Facades\Cache::tags(['products'])->flush();

            return response()->json(['message' => 'Đặt hàng thành công', 'mahd' => $mahd]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi đặt hàng: ' . $e->getMessage()], 500);
        }
    }
}
