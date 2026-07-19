<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GhnController extends Controller
{
    private $apiUrl = 'https://online-gateway.ghn.vn/shiip/public-api';

    public function getProvinces()
    {
        $response = Http::withoutVerifying()->withHeaders([
            'Token' => env('GHN_API_TOKEN'),
        ])->get($this->apiUrl . '/master-data/province');

        return $response->json();
    }

    public function getDistricts(Request $request)
    {
        $response = Http::withoutVerifying()->withHeaders([
            'Token' => env('GHN_API_TOKEN'),
        ])->get($this->apiUrl . '/master-data/district', [
            'province_id' => $request->province_id
        ]);

        return $response->json();
    }

    public function getWards(Request $request)
    {
        $response = Http::withoutVerifying()->withHeaders([
            'Token' => env('GHN_API_TOKEN'),
        ])->get($this->apiUrl . '/master-data/ward', [
            'district_id' => $request->district_id
        ]);

        return $response->json();
    }

    public function calculateFee(Request $request)
    {
        $request->validate([
            'to_ward_code' => 'required',
            'to_district_id' => 'required',
            'weight' => 'required|integer',
        ]);

        $response = Http::withoutVerifying()->withHeaders([
            'Token' => env('GHN_API_TOKEN'),
            'ShopId' => env('GHN_SHOP_ID')
        ])->post($this->apiUrl . '/v2/shipping-order/fee', [
            'service_type_id' => 2, // Standard delivery
            'insurance_value' => $request->insurance_value ?? 0,
            'coupon' => null,
            'to_ward_code' => $request->to_ward_code,
            'to_district_id' => $request->to_district_id,
            'weight' => $request->weight,
            'length' => 20,
            'width' => 20,
            'height' => 10
        ]);

        return $response->json();
    }

    public function webhook(Request $request)
    {
        // Nhận dữ liệu từ GHN Webhook (GHN gọi POST tới endpoint này)
        $orderCode = $request->input('OrderCode');
        $status = $request->input('Status'); // GHN Status có thể là 'delivered', 'cancel', 'picking', 'delivering'...

        if (!$orderCode || !$status) {
            return response()->json(['message' => 'Thiếu dữ liệu bắt buộc'], 400);
        }

        // Tìm đơn hàng tương ứng với MAVANDON
        $invoice = \App\Models\HdBan::where('MAVANDON', $orderCode)->first();
        
        if ($invoice) {
            // Ánh xạ trạng thái GHN sang hệ thống
            if ($status === 'delivered') {
                $invoice->TRANGTHAITHANHTOAN = 'Đã hoàn thành';
                $invoice->save();
            } elseif ($status === 'cancel') {
                if ($invoice->TRANGTHAITHANHTOAN !== 'Đã hủy') {
                    $invoice->TRANGTHAITHANHTOAN = 'Đã hủy';
                    $invoice->save();
                    
                    // Hoàn lại số lượng tồn kho tự động
                    $items = \App\Models\ChiTietHdBan::where('MAHD', $invoice->MAHD)->get();
                    foreach ($items as $item) {
                        $sp = \App\Models\SanPham::find($item->MASP);
                        if ($sp) {
                            $sp->SOLUONG += $item->SOLUONG;
                            $sp->save();
                        }
                    }
                    
                    // Xóa cache vì tồn kho đã thay đổi
                    \Illuminate\Support\Facades\Cache::tags(['products'])->flush();
                }
            } elseif (in_array($status, ['picking', 'delivering'])) {
                $invoice->TRANGTHAITHANHTOAN = 'Đang giao';
                $invoice->save();
            }
            
            return response()->json(['message' => 'Xử lý webhook GHN thành công']);
        }

        return response()->json(['message' => 'Không tìm thấy đơn hàng với mã vận đơn này'], 404);
    }
}
