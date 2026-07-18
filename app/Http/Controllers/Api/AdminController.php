<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\TaiKhoan;
use App\Models\NhanVien;

class AdminController extends Controller
{
    /**
     * View a list of all accounts
     */
    public function listUsers()
    {
        $users = TaiKhoan::with('khachhang', 'nhanvien', 'vaitro')->get();
        return response()->json($users);
    }

    /**
     * Create employee accounts (Admins only)
     */
    public function createStaff(Request $request)
    {
        $request->validate([
            'USERNAME' => 'required|string|unique:taikhoan,USERNAME',
            'PASSWORD' => 'required|string|min:6',
            'EMAIL'    => 'required|email|unique:taikhoan,EMAIL',
            'HOTEN'    => 'required|string',
            'CHUCVU'   => 'required|string',
            'LUONG'    => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            TaiKhoan::create([
                'USERNAME' => $request->USERNAME,
                'PASSWORD' => $request->PASSWORD,
                'MAROLE'   => 1, // 1: Staff
                'EMAIL'    => $request->EMAIL,
            ]);

            NhanVien::create([
                'USERNAME' => $request->USERNAME,
                'HOTEN'    => $request->HOTEN,
                'CHUCVU'   => $request->CHUCVU,
                'LUONG'    => $request->LUONG,
            ]);

            DB::commit();
            return response()->json(['message' => 'Tạo tài khoản nhân viên thành công'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Tạo nhân viên thất bại'], 500);
        }
    }
    /**
     * Customer ranking logic based on accumulated points.
     */
    public function promoteCustomer($makh)
    {
        $khachHang = \App\Models\KhachHang::find($makh);
        
        if (!$khachHang) {
            return response()->json(['error' => 'Không tìm thấy khách hàng'], 404);
        }

        $diem = $khachHang->DIEMTICHLUY ?? 0;
        $hang = 'Thường';

        if ($diem >= 10000) {
            $hang = 'Kim Cương';
        } elseif ($diem >= 5000) {
            $hang = 'Vàng';
        } elseif ($diem >= 1000) {
            $hang = 'Bạc';
        }

        return response()->json([
            'MAKH' => $khachHang->MAKH,
            'HOTEN' => $khachHang->HOTEN,
            'DIEMTICHLUY' => $diem,
            'HANG_HIEN_TAI' => $hang,
            'message' => 'Logic thăng hạng hoạt động bình thường'
        ]);
    }
}
