<?php

namespace App\Jobs;

use App\Clients\AlipayClient;
use App\Http\Controllers\OrderController;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckOrderIsPayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("进入支付轮询job", [$this->id, $this->attempts()]);
        try {
            if ($this->id) {
                $result = AlipayClient::queryStatus($this->id);
                if ($result['success']) {
                    $status = data_get($result['result'], 'trade_status');
                    $price = data_get($result, 'result.total_amount');
                    if ($price && in_array($status, array_keys(OrderController::$statusMessage))) {
                        $order = Order::query()->where('notify_id', $this->id)->first();
                        if ($order) $order->tradeSuccess($price);
                    }
                } else {
                    throw new \Exception("订单未支付");
                }
            }
        } catch (\Throwable $exception) {
            if ($this->attempts() > 3) {
                // hard fail after 9 attempts
                throw $exception;
            }

            // requeue this job to be executes
            // in 3 minutes (180 seconds) from now
            $this->release(60);
            return;
        }
    }
}
