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
            }

            \Illuminate\Support\Facades\DB::commit();

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
            $invoice->TRANGTHAITHANHTOAN = $request->trangThai;
            $invoice->save();
        }
        return response()->json(['message' => 'Cập nhật thành công']);
    }
}
