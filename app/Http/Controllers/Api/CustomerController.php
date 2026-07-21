<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KhachHang;
use App\Models\TaiKhoan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = KhachHang::withCount('hdbans as totalOrders')
            ->select('*')
            ->selectSub(function($query) {
                $query->from('hdban')
                      ->whereColumn('hdban.MAKH', 'khachhang.MAKH')
                      ->whereIn('TRANGTHAITHANHTOAN', ['Đã hoàn thành', 'Đã duyệt', 'Đang giao'])
                      ->selectRaw('COALESCE(SUM(TONGTIEN), 0)');
            }, 'totalSpent')
            ->get();
            
        return response()->json($customers->map(function($c) {
            $rank = 'Đồng';
            if ($c->DIEMTICHLUY >= 1000) $rank = 'Thách đấu';
            elseif ($c->DIEMTICHLUY >= 500) $rank = 'Kim cương';
            elseif ($c->DIEMTICHLUY >= 200) $rank = 'Vàng';
            elseif ($c->DIEMTICHLUY >= 50) $rank = 'Bạc';

            return [
                'maKH' => $c->MAKH,
                'hoTen' => $c->HOTEN,
                'sdt' => $c->SDT,
                'email' => $c->taikhoan ? $c->taikhoan->EMAIL : null,
                'ngaySinh' => $c->NGAYSINH,
                'gioiTinh' => $c->GioiTinh,
                'diaChi' => $c->DIACHI,
                'diemTichLuy' => $c->DIEMTICHLUY,
                'membershipRank' => $rank,
                'totalOrders' => $c->totalOrders,
                'totalSpent' => $c->totalSpent,
                'macuahang' => $c->MACUAHANG
            ];
        }));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hoTen' => 'required|string',
            'sdt' => 'required|string',
        ]);

        $maCuaHang = $request->macuahang ?? 1;

        // Nếu admin/staff đang login, lấy MACUAHANG của họ
        $user = Auth::guard('api')->user();
        if ($user && in_array($user->MAROLE, [0, 1, 2])) {
            $nhanVien = \App\Models\NhanVien::where('USERNAME', $user->USERNAME)->first();
            if ($nhanVien && $nhanVien->MACUAHANG) {
                $maCuaHang = $nhanVien->MACUAHANG;
            }
        }

        $maKH = 'KH' . substr(time(), -8);
        $kh = KhachHang::create([
            'MAKH' => $maKH,
            'HOTEN' => $request->hoTen,
            'SDT' => $request->sdt,
            'NGAYSINH' => $request->ngaySinh,
            'GioiTinh' => $request->gioiTinh,
            'DIACHI' => $request->diaChi,
            'DIEMTICHLUY' => 0,
            'MACUAHANG' => $maCuaHang
        ]);

        return response()->json($kh);
    }

    public function update(Request $request, $id)
    {
        $kh = KhachHang::find($id);
        if (!$kh) return response()->json(['error' => 'Not found'], 404);

        $kh->update([
            'HOTEN' => $request->hoTen ?? $kh->HOTEN,
            'SDT' => $request->sdt ?? $kh->SDT,
            'NGAYSINH' => $request->ngaySinh ?? $kh->NGAYSINH,
            'GioiTinh' => $request->gioiTinh ?? $kh->GioiTinh,
            'DIACHI' => $request->diaChi ?? $kh->DIACHI,
            'MACUAHANG' => $request->macuahang ?? $kh->MACUAHANG
        ]);

        return response()->json($kh);
    }

    public function destroy($id)
    {
        $kh = KhachHang::find($id);
        if ($kh) $kh->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
