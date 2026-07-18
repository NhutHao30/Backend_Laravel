<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoaiSanPham;

class CategoryController extends Controller
{
    /**
     * Get a list of all categories (shared by both Customers & Admin)
     */
    public function index()
    {
        return response()->json(LoaiSanPham::all());
    }

    /**
     * Admin: Add new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'MALOAI' => 'required|string|unique:loaisanpham,MALOAI',
            'TENLOAI' => 'required|string|unique:loaisanpham,TENLOAI',
        ]);

        $category = LoaiSanPham::create([
            'MALOAI' => $request->MALOAI,
            'TENLOAI' => $request->TENLOAI
        ]);

        return response()->json(['message' => 'Thêm danh mục thành công', 'data' => $category], 201);
    }

    /**
     * Admin: Update catalog
     */
    public function update(Request $request, $id)
    {
        $category = LoaiSanPham::find($id);
        if (!$category) return response()->json(['error' => 'Không tìm thấy danh mục'], 404);

        $request->validate([
            'TENLOAI' => 'required|string|unique:loaisanpham,TENLOAI,' . $id . ',MALOAI',
        ]);

        $category->update([
            'TENLOAI' => $request->TENLOAI
        ]);

        return response()->json(['message' => 'Cập nhật danh mục thành công', 'data' => $category]);
    }

    /**
     * Admin: Delete catalog
     */
    public function destroy($id)
    {
        $category = LoaiSanPham::find($id);
        if (!$category) return response()->json(['error' => 'Không tìm thấy danh mục'], 404);

        // Check if any products belong to this category (If so, do not delete them).
        if ($category->sanphams()->count() > 0) {
            return response()->json(['error' => 'Không thể xóa danh mục đang có sản phẩm'], 400);
        }

        $category->delete();
        return response()->json(['message' => 'Xóa danh mục thành công']);
    }
}
