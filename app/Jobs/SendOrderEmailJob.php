<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $mahd;
    protected $tongTien;
    protected $hoten;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $mahd, $tongTien, $hoten)
    {
        $this->email = $email;
        $this->mahd = $mahd;
        $this->tongTien = $tongTien;
        $this->hoten = $hoten;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (empty($this->email)) return;

        $subject = "Xác nhận đặt hàng thành công - Dola Bakery";
        $htmlContent = "
            <h2>Chào {$this->hoten},</h2>
            <p>Cảm ơn bạn đã đặt hàng tại <b>Dola Bakery</b>.</p>
            <p>Mã đơn hàng của bạn là: <b>{$this->mahd}</b></p>
            <p>Tổng tiền thanh toán: <b>" . number_format($this->tongTien, 0, ',', '.') . " VNĐ</b></p>
            <p>Đơn hàng của bạn đang được xử lý. Bạn có thể đăng nhập vào website để xem chi tiết trạng thái giao hàng.</p>
            <br>
            <p>Trân trọng,<br>Đội ngũ Dola Bakery</p>
        ";

        Mail::html($htmlContent, function ($message) use ($subject) {
            $message->to($this->email)
                    ->subject($subject);
        });
    }
}
