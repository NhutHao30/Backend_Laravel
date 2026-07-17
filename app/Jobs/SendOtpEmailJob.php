<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOtpEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $otpCode;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $otpCode)
    {
        $this->email = $email;
        $this->otpCode = $otpCode;
    }

    /**
     * Execute the job. (Worker sẽ chạy hàm này)
     */
    public function handle(): void
    {
        // Gửi email thật (Yêu cầu cấu hình MAIL_ trong .env)
        Mail::raw("Mã OTP lấy lại mật khẩu của bạn là: {$this->otpCode}", function ($message) {
            $message->to($this->email)
                    ->subject('Yêu cầu cấp lại mật khẩu - Dola Bakery');
        });
    }
}
