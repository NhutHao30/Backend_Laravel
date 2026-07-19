<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\HdBan;
use App\Models\ChiTietHdBan;

class PushOrderToGhnJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoiceId;
    protected $wardCode;
    protected $districtId;
    protected $addressDisplay;

    /**
     * Create a new job instance.
     */
    public function __construct($invoiceId, $wardCode, $districtId, $addressDisplay)
    {
        $this->invoiceId = $invoiceId;
        $this->wardCode = $wardCode;
        $this->districtId = $districtId;
        $this->addressDisplay = $addressDisplay;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $invoice = HdBan::with('khachhang')->find($this->invoiceId);
        if (!$invoice || !empty($invoice->MAVANDON)) {
            return;
        }

        $items = ChiTietHdBan::with('sanpham')->where('MAHD', $this->invoiceId)->get();
        $khachHang = $invoice->khachhang;
        
        $phone = $khachHang->SDT ?? '0901234567';
        if (!preg_match('/^(0[3|5|7|8|9])+([0-9]{8})$/', $phone)) {
            $phone = '0901234567';
        }

        // Xác định hình thức thanh toán cho GHN
        $isCOD = ($invoice->PHUONGTHUCTHANHTOAN === 'COD (Giao hàng)');
        $paymentTypeId = $isCOD ? 2 : 1; // 2: Thu hộ, 1: Không thu hộ
        $codAmount = $isCOD ? (int) $invoice->TONGTIEN : 0;

        $ghnResponse = Http::withoutVerifying()->withHeaders([
            'Token' => env('GHN_API_TOKEN'),
            'ShopId' => env('GHN_SHOP_ID')
        ])->post('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create', [
            'payment_type_id' => $paymentTypeId, 
            'note' => $this->addressDisplay,
            'required_note' => 'CHOXEMHANGKHONGTHU',
            'return_phone' => '0353144481',
            'return_address' => 'Dola Bakery', 
            'to_name' => $khachHang ? $khachHang->HOTEN : 'Khách vãng lai',
            'to_phone' => $phone,
            'to_address' => $this->addressDisplay,
            'to_ward_code' => $this->wardCode,
            'to_district_id' => $this->districtId,
            'cod_amount' => $codAmount, 
            'weight' => 1000,
            'length' => 20,
            'width' => 20,
            'height' => 10,
            'service_type_id' => 2,
            'items' => $items->map(function($item) {
                return [
                    'name' => 'Bánh ngọt Dola Bakery',
                    'quantity' => (int) $item->SOLUONG,
                    'price' => (int) $item->DONGIA,
                    'weight' => 500
                ];
            })->toArray()
        ]);

        if ($ghnResponse->successful() && isset($ghnResponse['data']['order_code'])) {
            $invoice->MAVANDON = $ghnResponse['data']['order_code'];
            $invoice->DonViVanChuyen = 'Giao Hàng Nhanh';
            $invoice->save();
        }
    }
}
