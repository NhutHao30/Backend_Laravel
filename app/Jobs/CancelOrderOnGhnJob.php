<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CancelOrderOnGhnJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderCode;

    /**
     * Create a new job instance.
     */
    public function __construct($orderCode)
    {
        $this->orderCode = $orderCode;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (empty($this->orderCode)) {
            return;
        }

        Http::withoutVerifying()->withHeaders([
            'Token' => env('GHN_API_TOKEN'),
            'ShopId' => env('GHN_SHOP_ID')
        ])->post('https://online-gateway.ghn.vn/shiip/public-api/v2/switch-status/cancel', [
            'order_codes' => [$this->orderCode]
        ]);
    }
}
