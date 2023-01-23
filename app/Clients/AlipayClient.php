<?php

namespace App\Clients;

use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use Alipay\EasySDK\Kernel\Config;


class AlipayClient
{
    public static $init = false;

    public static function initOptiosn()
    {
        if (!self::$init) {
            //1. 设置参数（全局只需设置一次）
            $options = self::getOptions();
//            dd($options);
            Factory::setOptions($options);
            self::$init = true;
        }
    }

    public static function getOptions()
    {
        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = config('alipay.gateway_url');
        $options->signType = config('alipay.sign_type');

        $options->appId = config('alipay.app_id');

        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        $options->merchantPrivateKey = config('alipay.merchant_private_key');

        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        $options->alipayPublicKey = config('alipay.alipay_public_key');
        $options->encryptKey = config('alipay.aes_key');
        $options->notifyUrl = "http://alipay.app.test/api/alipay/notify";
//        dd($options);
        return $options;
    }


    /**
     */
    public function __construct()
    {
        //1. 设置参数（全局只需设置一次）
    }

    public static function payment($order)
    {
        $privateKey = config('alipay.merchant_private_key');
        $alipayPublicKey = config('alipay.alipay_public_key');
        $alipayConfig = new \AlipayConfig();
        $alipayConfig->setServerUrl(config('alipay.gateway_url'));
        $alipayConfig->setAppId(config('alipay.app_id'));
        $alipayConfig->setPrivateKey($privateKey);
        $alipayConfig->setFormat("json");
        $alipayConfig->setAlipayPublicKey($alipayPublicKey);
        $alipayConfig->setCharset("UTF-8");
        $alipayConfig->setSignType("RSA2");
        $alipayClient = new \AopClient($alipayConfig);
        $request = new \AlipayTradePrecreateRequest();


        $url = route('api.alipay.notify');
        $request->setNotifyUrl($url);
        $totalAmount = $order->price + $order->fee_price;

        $data = [
            'out_trade_no' => $order->notify_id,
            'total_amount' => floatval($totalAmount),
            'subject' => "Domain And Sll fee",
            'timeout_express' => "1d",
        ];
        $request->setBizContent(json_encode($data));
//        $request->setBizContent("{" .
//            "\"out_trade_no\":\"20150320010101001\"," .
//            "\"total_amount\":\"88.88\"," .
//            "\"subject\":\"Iphone6 16G\"" .
//            "}");
        $alipayClient->debugInfo =true;
        $responseResult = $alipayClient->execute($request);

        $responseApiName = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $response = data_get($responseResult, $responseApiName);
        $code = data_get($response , 'code');

        return [
            'success' => $response && $code == 10000,
            'result' => $response,
        ];
    }


    public static function queryStatus($id) {


        $privateKey = config('alipay.merchant_private_key');
        $alipayPublicKey = config('alipay.alipay_public_key');
        $alipayConfig = new \AlipayConfig();
        $alipayConfig->setServerUrl(config('alipay.gateway_url'));
        $alipayConfig->setAppId(config('alipay.app_id'));
        $alipayConfig->setPrivateKey($privateKey);
        $alipayConfig->setFormat("json");
        $alipayConfig->setAlipayPublicKey($alipayPublicKey);
        $alipayConfig->setCharset("UTF-8");
        $alipayConfig->setSignType("RSA2");
        $alipayClient = new \AopClient($alipayConfig);
        $request = new \AlipayTradeQueryRequest();
        $request->setBizContent("{" .
            "\"out_trade_no\":\"{$id}\"" .
            "}");
        $responseResult = $alipayClient->execute($request);
        $responseApiName = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $response = data_get($responseResult, $responseApiName);
        $code = data_get($response , 'code');

        return [
            'success' => $response && $code == 10000,
            'result' => $response,
        ];

    }

}
