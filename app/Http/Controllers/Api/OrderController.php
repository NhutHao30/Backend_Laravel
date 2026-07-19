<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function storePOS(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'cart.*.id' => 'required|string',
            'cart.*.quantity' => 'required|numeric|min:1',
            'cart.*.price' => 'required|numeric|min:0',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Tính tổng tiền
            $total = 0;
            foreach ($request->cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Create a sales invoice (HdBan) with a maximum length of 10 characters (varchar(10))
            $mahd = 'HD' . substr(time(), -8);
            // Identify the customer; if none exists, create a default visitor
            $makh = $request->customerId;
            if (empty($makh)) {
                $defaultKhach = \App\Models\KhachHang::firstOrCreate(
                    ['SDT' => '0000000000'],
                    [
                        'MAKH' => 'KH000000',
                        'HOTEN' => 'Khách vãng lai (POS)',
                        'DIEMTICHLUY' => 0
                    ]
                );
                $makh = $defaultKhach->MAKH;
            }

            \App\Models\HdBan::create([
                'MAHD' => $mahd,
                'NGAYLAP' => now(),
                'TONGTIEN' => $total,
                'PHUONGTHUCTHANHTOAN' => $request->paymentMethod === 'MOMO' ? 'Ví Momo P2P' : ($request->paymentMethod === 'VIETQR' ? 'Chuyển khoản VietQR' : ($request->paymentMethod === 'COD_GIAO' ? 'COD (Giao hàng)' : 'Tiền mặt')),
                'TRANGTHAITHANHTOAN' => $request->paymentMethod === 'COD_GIAO' ? 'Đang giao' : 'Đã hoàn thành',
                'GHICHU' => 'Khách mua tại quầy (POS)',
                'MAKH' => $makh,
                'MAVANDON' => null,
            ]);

            // Save Sales Invoice Details
            foreach ($request->cart as $item) {
                \App\Models\ChiTietHdBan::create([
                    'MAHD' => $mahd,
                    'MASP' => $item['id'],
                    'SOLUONG' => $item['quantity'],
                    'DONGIA' => $item['price'],
                ]);
                
                // Trừ tồn kho thủ công (không dựa vào Trigger nữa để đảm bảo chính xác)
                $sp = \App\Models\SanPham::find($item['id']);
                if ($sp) {
                    $sp->SOLUONG -= $item['quantity'];
                    $sp->save();
                }
            }
            
            // Tích điểm cho khách hàng (Nếu không phải Khách vãng lai)
            if ($makh !== 'KH000000') {
                $khachHang = \App\Models\KhachHang::find($makh);
                if ($khachHang) {
                    $diemCong = floor($total / 10000); // 10k = 1 điểm
                    $khachHang->DIEMTICHLUY += $diemCong;
                    $khachHang->save();
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            
            // Xóa cache vì tồn kho đã thay đổi
            \Illuminate\Support\Facades\Cache::tags(['products'])->flush();

            return response()->json([
                'message' => 'Thanh toán thành công',
                'mahd' => $mahd
            ], 200);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['error' => 'Lỗi tạo hóa đơn: ' . $e->getMessage()], 500);
        }
    }

    // Get the list of invoices
    public function index(Request $request)
    {
        $query = \App\Models\HdBan::with('khachhang');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('MAHD', 'like', "%{$search}%")
                  ->orWhere('MAKH', 'like', "%{$search}%");
            });
        }

        if ($request->has('trangThai') && !empty($request->trangThai)) {
            $query->where('TRANGTHAITHANHTOAN', $request->trangThai);
        }

        if ($request->has('maKH') && !empty($request->maKH)) {
            $query->where('MAKH', $request->maKH);
        }

        $size = $request->input('size', 100);
        $invoices = $query->orderBy('NGAYLAP', 'desc')->paginate($size);

        // Normalize the response to the desired frontend format.
        $formattedInvoices = collect($invoices->items())->map(function ($invoice) {
            return [
                'maHD' => $invoice->MAHD,
                'ngayLap' => $invoice->NGAYLAP,
                'tongTien' => $invoice->TONGTIEN,
                'trangThai' => $invoice->TRANGTHAITHANHTOAN,
                'ghiChu' => $invoice->GHICHU,
                'donViVanChuyen' => $invoice->DonViVanChuyen,
                'maVanDon' => $invoice->MAVANDON,
                'maKH' => $invoice->MAKH,
                'hoTenKH' => $invoice->khachhang ? $invoice->khachhang->HOTEN : 'Khách vãng lai'
            ];
        });

        return response()->json([
            'content' => $formattedInvoices,
            'totalElements' => $invoices->total(),
            'totalPages' => $invoices->lastPage(),
        ]);
    }

    public function show($id)
    {
        $invoice = \App\Models\HdBan::find($id);
        if (!$invoice) return response()->json(['message' => 'Not found'], 404);
        return response()->json($invoice);
    }

    public function getDetails($id)
    {
        $details = \App\Models\ChiTietHdBan::with('sanpham')->where('MAHD', $id)->get();
        $formatted = $details->map(function ($item) {
            return [
                'maSP' => $item->MASP,
                'tenSP' => $item->sanpham ? $item->sanpham->TENSP : 'Sản phẩm ' . $item->MASP,
                'soLuong' => $item->SOLUONG,
                'donGia' => $item->DONGIA,
                'thanhTien' => $item->SOLUONG * $item->DONGIA // Tính lại
            ];
        });
        return response()->json($formatted);
    }

    public function updateStatus(Request $request, $id)
    {
        $invoice = \App\Models\HdBan::find($id);
        if (!$invoice) return response()->json(['message' => 'Not found'], 404);

        if ($request->has('trangThai')) {
            $newStatus = $request->trangThai;
            
            // Nếu trạng thái là Đã duyệt/Đang giao và chưa có mã vận đơn GHN, thì đẩy sang GHN
            if (in_array($newStatus, ['Đã duyệt', 'Đang giao', 'Đã xác nhận']) && empty($invoice->MAVANDON)) {
                
                // Parse ward_code and district_id from GHICHU
                $wardCode = null;
                $districtId = null;
                if (preg_match('/\[GHN_WARD:(.*?),GHN_DIST:(.*?)\]/', $invoice->GHICHU, $matches)) {
                    $wardCode = $matches[1];
                    $districtId = (int) $matches[2];
                }

                if ($wardCode && $districtId) {
                    // Tách địa chỉ hiển thị khỏi chuỗi GHICHU
                    $addressDisplay = preg_replace('/\[GHN_WARD:.*?\]/', '', $invoice->GHICHU);

                    // Đẩy job tạo đơn vào hàng đợi
                    \App\Jobs\PushOrderToGhnJob::dispatch($id, $wardCode, $districtId, $addressDisplay);
                }
            }

            // Logic Hủy đơn hàng và hoàn kho
            if ($newStatus === 'Đã hủy' && $invoice->TRANGTHAITHANHTOAN !== 'Đã hủy') {
                // 1. Hoàn lại số lượng tồn kho
                $items = \App\Models\ChiTietHdBan::where('MAHD', $id)->get();
                foreach ($items as $item) {
                    $sp = \App\Models\SanPham::find($item->MASP);
                    if ($sp) {
                        $sp->SOLUONG += $item->SOLUONG;
                        $sp->save();
                    }
                }
                
                // Xóa cache vì tồn kho đã thay đổi
                \Illuminate\Support\Facades\Cache::tags(['products'])->flush();

                // 2. Hủy đơn trên GHN nếu đã có mã vận đơn
                if (!empty($invoice->MAVANDON) && $invoice->DonViVanChuyen === 'Giao Hàng Nhanh') {
                    \App\Jobs\CancelOrderOnGhnJob::dispatch($invoice->MAVANDON);
                }
            }

            $invoice->TRANGTHAITHANHTOAN = $newStatus;
            $invoice->save();
        }
        return response()->json(['message' => 'Cập nhật thành công', 'mavandon' => $invoice->MAVANDON]);
    }

    /**
     * Customer: Xem danh sách đơn hàng của mình
     */
    public function myOrders()
    {
        $user = \Illuminate\Support\Facades\Auth::guard('api')->user();
        if (!$user || $user->MAROLE != 2) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $khachHang = \App\Models\KhachHang::where('USERNAME', $user->USERNAME)->first();
        if (!$khachHang) {
            return response()->json(['content' => []]);
        }

        $orders = \App\Models\HdBan::where('MAKH', $khachHang->MAKH)
            ->orderBy('NGAYLAP', 'desc')
            ->get();
            
        // Map data để Frontend dễ dùng (giống cấu trúc cũ)
        $mapped = $orders->map(function ($order) {
            return [
                'maHD' => $order->MAHD,
                'ngayLap' => $order->NGAYLAP,
                'tongTien' => $order->TONGTIEN,
                'hinhThucThanhToan' => $order->PHUONGTHUCTHANHTOAN,
                'trangThai' => $order->TRANGTHAITHANHTOAN,
                'diaChiGiao' => $order->GHICHU,
                'maVanDon' => $order->MAVANDON
            ];
        });

        return response()->json(['content' => $mapped]);
    }

    /**
     * Customer: Xem chi tiết đơn hàng của mình
     */
    public function getMyOrderDetails($id)
    {
        $user = \Illuminate\Support\Facades\Auth::guard('api')->user();
        if (!$user || $user->MAROLE != 2) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $khachHang = \App\Models\KhachHang::where('USERNAME', $user->USERNAME)->first();
        if (!$khachHang) {
            return response()->json(['error' => 'Không tìm thấy KH'], 404);
        }

        $order = \App\Models\HdBan::where('MAHD', $id)->where('MAKH', $khachHang->MAKH)->first();
        if (!$order) {
            return response()->json(['error' => 'Không tìm thấy đơn hàng'], 404);
        }

        $details = \App\Models\ChiTietHdBan::with('sanpham')->where('MAHD', $id)->get();
        
        $mapped = $details->map(function ($detail) {
            return [
                'maHD' => $detail->MAHD,
                'maSP' => $detail->MASP,
                'tenSP' => $detail->sanpham ? $detail->sanpham->TENSP : 'Sản phẩm đã bị xóa',
                'hinhAnh' => $detail->sanpham ? $detail->sanpham->HINHANH : null,
                'soLuong' => $detail->SOLUONG,
                'donGia' => $detail->DONGIA,
                'thanhTien' => $detail->SOLUONG * $detail->DONGIA
            ];
        });

        return response()->json($mapped);
    }
}
