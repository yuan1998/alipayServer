<?php

namespace App\Http\Controllers;

use App\Clients\AlipayClient;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public $statusMessage = [
        'TRADE_SUCCESS' => '支付成功.!',
        'WAIT_BUYER_PAY' => '订单已拉起,请支付.支付成功后请再次点击按钮检查支付状态.!'
    ];

    public function alipayCreate(Request $request)
    {

        try {
            if ($request->get('pay_url')) {
                $id = $request->get('notify_id');
                $result = AlipayClient::queryStatus($id);
                if ($result['success']) {
                    $status = data_get($result['result'], 'trade_status');
                    $msg = data_get($this->statusMessage, $status, "未知状态 $status");
                    $price = data_get($result, 'result.total_amount');
                    if ($price && in_array($status, array_keys($this->statusMessage))) {
                        $order = Order::query()->where('notify_id', $id)->first();
                        if ($order) $order->tradeSuccess($price);
                    }

                    return response()->json([
                        'status' => 0,
                        'msg' => $msg,
                        'data' => [
                            'message' => $msg,
                            'json' => json_encode($result['result'])
                        ]
                    ]);

                } else {
                    $msg = data_get($result['result'], 'sub_msg');
                    return response()->json([
                        'status' => $result['success'] ? 0 : 1,
                        'msg' => $msg ?: '订单尚未拉起,请扫码支付',
                        'data' => [
                            'message' => $msg ?: '订单尚未拉起,请扫码支付',
                            'json' => json_encode($result['result'])
                        ]
                    ]);
                }

            }
            $order = Order::createOrder($request);
            $result = AlipayClient::payment($order);
            if ($result['success']) {
                return response()->json([
                    'status' => 0,
                    'msg' => '获取订单成功.请在支付成功后再次点击按钮',
                    'data' => [
                        "message" => '获取订单成功.请在支付成功后再次点击按钮',
                        "pay_url" => data_get($result['result'], "qr_code")
                    ]
                ]);
            }

            $msg = data_get($result, 'result.sub_msg', '拉起支付失败!!');

            return response()->json([
                'status' => 1,
                'msg' => $msg,
                'data' => [
                    'message' => $msg
                ]
            ]);

        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return response()->json([
                "status" => 1,
                "msg" => $message,
                "data" => [
                    "message" => $message
                ]
            ]);
        }

    }

    public function alipayNotify(Request $request)
    {
        Log::debug('异步回调.');
        $id = $request->get('out_trade_no');
        $order = Order::query()
            ->where('notify_id', $id)
            ->first();

        if (!$order) {
            Log::error("!!!!!! 订单ID 不存在", [$id]);
            return 'fail';
        }
        $status = $request->get('trade_status');
        if ($status === 'TRADE_SUCCESS' || $status === 'TRADE_FINISHED') {
            $price = $request->get('total_amount', 0);
            $order->tradeSuccess($price);
            return 'success';
        }
        return 'fail';
    }

    public static function callNotifyUrl($url, $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $body = curl_exec($curl);

        curl_close($curl);

        return $body;
    }

}
