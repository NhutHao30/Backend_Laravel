<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\HdBan;

class ProcessOrderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mahd;

    /**
     * Create a new job instance.
     */
    public function __construct($mahd)
    {
        $this->mahd = $mahd;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $invoice = HdBan::with('khachhang.taikhoan')->where('MAHD', $this->mahd)->first();

        if ($invoice && $invoice->khachhang && $invoice->khachhang->taikhoan) {
            $email = $invoice->khachhang->taikhoan->EMAIL;

            if ($email) {
                Mail::raw("Chào {$invoice->khachhang->HOTEN},\n\nCảm ơn bạn đã đặt hàng tại Dola Bakery.\nMã đơn hàng của bạn là: {$invoice->MAHD}\nTổng tiền: " . number_format($invoice->TONGTIEN) . "đ.\n\nĐơn hàng của bạn đang được xử lý.", function ($message) use ($email) {
                    $message->to($email)
                            ->subject('Xác nhận đơn hàng từ Dola Bakery');
                });
            }
        }
    }
}
