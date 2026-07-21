<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CuaHang;
use App\Models\NhanVien;
use App\Models\HdBan;
use App\Models\TonKhoCuaHang;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function index()
    {
        $stores = CuaHang::all();
        $result = [];

        foreach ($stores as $store) {
            // Tính tổng doanh thu
            $revenue = HdBan::where('MACUAHANG', $store->MACUAHANG)
                            ->whereIn('TRANGTHAITHANHTOAN', ['Đã hoàn thành', 'Đã duyệt', 'Đang giao'])
                            ->sum('TONGTIEN');

            // Đếm số lượng khách hàng duy nhất đã mua tại cửa hàng này
            $customerCount = HdBan::where('MACUAHANG', $store->MACUAHANG)
                                  ->distinct('MAKH')
                                  ->count('MAKH');

            // Danh sách nhân viên
            $employees = NhanVien::where('MACUAHANG', $store->MACUAHANG)
                                 ->where('TRANGTHAI', '!=', 'Nghỉ việc')
                                 ->select('USERNAME', 'HOTEN', 'CHUCVU', 'LUONG')
                                 ->get();

            // Tồn kho sản phẩm
            $inventory = TonKhoCuaHang::where('MACUAHANG', $store->MACUAHANG)
                                      ->join('sanpham', 'tonkho_cuahang.MASP', '=', 'sanpham.MASP')
                                      ->select('sanpham.MASP', 'sanpham.TENSP', 'tonkho_cuahang.SOLUONG_TON')
                                      ->get();

            $totalSalary = $employees->sum('LUONG');

            // Lấy thông tin chấm công tháng hiện tại của nhân viên trong cửa hàng
            $usernames = $employees->pluck('USERNAME');
            $chamcongs = \App\Models\ChamCong::whereIn('USERNAME', $usernames)
                                ->whereMonth('NGAYCHAMCONG', now()->month)
                                ->whereYear('NGAYCHAMCONG', now()->year)
                                ->get();
            $totalDaysWorked = $chamcongs->where('TRANGTHAI', 1)->count();
            $totalDaysOff = $chamcongs->where('TRANGTHAI', 0)->count();

            // Tính số ngày công/nghỉ cho TỪNG nhân viên
            foreach ($employees as $emp) {
                $emp->workingDays = $chamcongs->where('USERNAME', $emp->USERNAME)->where('TRANGTHAI', 1)->count();
                $emp->absentDays = $chamcongs->where('USERNAME', $emp->USERNAME)->where('TRANGTHAI', 0)->count();
            }

            $newCustomers = \App\Models\KhachHang::where('MACUAHANG', $store->MACUAHANG)
                                ->where('created_at', '>=', now()->subDays(30))
                                ->count();

            // Biểu đồ Doanh thu và Hóa đơn trong 30 ngày gần nhất
            $startDate = now()->subDays(29)->startOfDay();
            $dailyStats = \App\Models\HdBan::where('MACUAHANG', $store->MACUAHANG)
                                ->whereIn('TRANGTHAITHANHTOAN', ['Đã hoàn thành', 'Đã duyệt', 'Đang giao'])
                                ->where('NGAYLAP', '>=', $startDate)
                                ->select(
                                    DB::raw('DATE(NGAYLAP) as date'), 
                                    DB::raw('SUM(TONGTIEN) as total'),
                                    DB::raw('COUNT(MAHD) as count')
                                )
                                ->groupBy('date')
                                ->get()
                                ->keyBy('date');
            
            $revenueChart = [];
            $invoiceChart = [];
            for ($i = 29; $i >= 0; $i--) {
                $dateStr = now()->subDays($i)->format('Y-m-d');
                $revenueChart[] = [
                    'date' => now()->subDays($i)->format('d/m'),
                    'total' => isset($dailyStats[$dateStr]) ? $dailyStats[$dateStr]->total : 0
                ];
                $invoiceChart[] = [
                    'date' => now()->subDays($i)->format('d/m'),
                    'count' => isset($dailyStats[$dateStr]) ? $dailyStats[$dateStr]->count : 0
                ];
            }

            $result[] = [
                'id' => $store->MACUAHANG,
                'name' => $store->TENCUAHANG,
                'address' => $store->DIACHI,
                'phone' => $store->SDT,
                'status' => $store->TRANGTHAI,
                'revenue' => $revenue,
                'customerCount' => $customerCount,
                'newCustomers' => $newCustomers,
                'totalSalary' => $totalSalary,
                'totalDaysWorked' => $totalDaysWorked,
                'totalDaysOff' => $totalDaysOff,
                'employees' => $employees,
                'inventory' => $inventory,
                'revenueChart' => $revenueChart,
                'invoiceChart' => $invoiceChart,
            ];
        }

        return response()->json($result);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:Đang hoạt động,Tạm đóng cửa,Đóng cửa vĩnh viễn'
        ]);

        $store = CuaHang::find($id);
        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        $store->TRANGTHAI = $request->status;
        $store->save();

        return response()->json(['message' => 'Cập nhật trạng thái thành công']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'TENCUAHANG' => 'required|string',
            'DIACHI' => 'required|string',
            'SDT' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $store = CuaHang::create([
                'TENCUAHANG' => $request->TENCUAHANG,
                'DIACHI' => $request->DIACHI,
                'SDT' => $request->SDT,
                'TRANGTHAI' => 'Đang hoạt động'
            ]);

            // Add all products to this store's inventory with 0 stock
            $products = \App\Models\SanPham::all();
            foreach ($products as $p) {
                TonKhoCuaHang::create([
                    'MACUAHANG' => $store->MACUAHANG,
                    'MASP' => $p->MASP,
                    'SOLUONG_TON' => 0
                ]);
            }
            DB::commit();
            return response()->json(['message' => 'Tạo chi nhánh thành công', 'store' => $store], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi tạo chi nhánh: ' . $e->getMessage()], 500);
        }
    }
}
