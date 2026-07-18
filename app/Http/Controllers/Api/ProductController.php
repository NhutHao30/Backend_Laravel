<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SanPham;
use App\Models\ChiTietHdNhap;
use App\Models\ChiTietHdBan;
use App\Services\UploadService;

class ProductController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Customers & Admins: View product list (Pagination, Filter price, Search name)
     */
    public function index(Request $request)
    {
        $query = SanPham::with('loaisanpham');

        // Search by product name (Supports both 'search' and 'keyword')
        $search = $request->input('search', $request->input('keyword'));
        if ($search) {
            $query->where('TENSP', 'like', '%' . $search . '%');
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('GIABAN', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('GIABAN', '<=', $request->max_price);
        }

        // Filter by category code (Supports both 'maloai' or 'category')
        $category = $request->input('maloai', $request->input('category'));
        if ($category) {
            $query->where('MALOAI', $category);
        }
        
        // Supports sorting by name or price (sortBy and direction)
        if ($request->has('sortBy')) {
            $sortBy = $request->input('sortBy');
            $direction = $request->input('direction', 'asc');
            
            // Map the correct column names in Laravel.
            if (strtolower($sortBy) == 'tensp') $sortBy = 'TENSP';
            if (strtolower($sortBy) == 'giaban') $sortBy = 'GIABAN';
            
            $query->orderBy($sortBy, $direction);
        } else {
            // Default sorting is latest.
            $query->orderBy('MASP', 'desc');
        }

        // Paginate 12 products per page.
        $products = $query->paginate(12);

        // Calculate the actual inventory for each returned product.
        $products->getCollection()->transform(function ($product) {
            $product->setAttribute('TONKHO_THUCTE', $this->calculateStock($product->MASP));
            return $product;
        });

        return response()->json($products);
    }

    /**
     * Customer: View product details
     */
    public function show($id)
    {
        // Add 'binhluans' if the comment/review table has a link.
        $product = SanPham::with(['loaisanpham'])->find($id);
        if (!$product) return response()->json(['error' => 'Không tìm thấy sản phẩm'], 404);

        $product->setAttribute('TONKHO_THUCTE', $this->calculateStock($product->MASP));
        
        return response()->json($product);
    }

    /**
     * Admin: Add new products (Includes image upload feature to MinIO)
     */
    public function store(Request $request)
    {
        $request->validate([
            'MASP' => 'required|string|unique:sanpham,MASP',
            'MALOAI' => 'required|exists:loaisanpham,MALOAI',
            'TENSP' => 'required|string|unique:sanpham,TENSP',
            'GIABAN' => 'required|numeric|min:0',
            'SOLUONG' => 'nullable|numeric|min:0',
            'DVT' => 'required|string',
            'GHICHU' => 'nullable|string',
            'HINHANH' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // Upload photos to MinIO via UploadService
        $imageUrl = $this->uploadService->uploadImage($request->file('HINHANH'), 'products');

        $product = SanPham::create([
            'MASP' => $request->MASP,
            'MALOAI' => $request->MALOAI,
            'TENSP' => $request->TENSP,
            'GIABAN' => $request->GIABAN,
            'DVT' => $request->DVT,
            'GHICHU' => $request->GHICHU,
            'HINHANH' => $imageUrl,
            'SOLUONG' => $request->input('SOLUONG', 0) // Get the quantity from the form, the default is 0.
        ]);

        return response()->json(['message' => 'Thêm sản phẩm thành công', 'data' => $product], 201);
    }

    /**
     * Admin: Update product
     */
    public function update(Request $request, $id)
    {
        $product = SanPham::find($id);
        if (!$product) return response()->json(['error' => 'Không tìm thấy sản phẩm'], 404);

        $request->validate([
            'MALOAI' => 'required|exists:loaisanpham,MALOAI',
            'TENSP' => 'required|string|unique:sanpham,TENSP,' . $id . ',MASP',
            'GIABAN' => 'required|numeric|min:0',
            'SOLUONG' => 'nullable|numeric|min:0',
            'DVT' => 'required|string',
            'GHICHU' => 'nullable|string',
            'HINHANH' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $dataUpdate = $request->except('HINHANH');

        // If you upload new photos
        if ($request->hasFile('HINHANH')) {
            // Delete old photos on MinIO
            $this->uploadService->deleteImage($product->HINHANH);
            // Upload new photos
            $dataUpdate['HINHANH'] = $this->uploadService->uploadImage($request->file('HINHANH'), 'products');
        }

        $product->update($dataUpdate);

        return response()->json(['message' => 'Cập nhật sản phẩm thành công', 'data' => $product]);
    }

    /**
     * Admin: delete product
     */
    public function destroy($id)
    {
        $product = SanPham::find($id);
        if (!$product) return response()->json(['error' => 'Không tìm thấy sản phẩm'], 404);

        try {
            // Remove all foreign key constraints (Shopping Cart, Discount, Invoice) to clean up the system.
            \App\Models\ChiTietGioHang::where('MASP', $id)->delete();
            \App\Models\GiamGia::where('MASP', $id)->delete();
            \App\Models\ChiTietHdBan::where('MASP', $id)->delete();
            \App\Models\ChiTietHdNhap::where('MASP', $id)->delete();

            // Delete photos on MinIO
            $this->uploadService->deleteImage($product->HINHANH);
            
            // Use the DB facade to perform a permanent (force delete) deletion, bypassing SoftDeletes.
            \Illuminate\Support\Facades\DB::table('sanpham')->where('MASP', $id)->delete();

            return response()->json(['message' => 'Đã xóa vĩnh viễn sản phẩm và các dữ liệu liên quan']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Internal function: Calculates the actual inventory of the product.
     * Inventory = Total Imports (SOLUONGTN) - Total Sales (SOLUONG)
     */
    public function calculateStock($masp)
    {
        // Calculate the total quantity of goods received.
        $tongNhap = ChiTietHdNhap::where('MASP', $masp)->sum('SOLUONGTN');
        
        // Calculate the total quantity sold (temporarily including all items, even those yet to be paid for, to deduct from inventory).
        $tongBan = ChiTietHdBan::where('MASP', $masp)->sum('SOLUONG');

        // Initial (base) declared quantity
        $sanPham = SanPham::find($masp);
        $soLuongBanDau = $sanPham ? $sanPham->SOLUONG : 0;

        return $soLuongBanDau + $tongNhap - $tongBan;
    }
}
