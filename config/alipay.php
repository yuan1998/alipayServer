<?php

return [
    'app_id'                   => env('ALIPAY_APP_ID'),
    'gateway_url'              => env('ALIPAY_GATEWAY_URL','https://openapi.alipaydev.com/gateway.do'),
    //沙箱环境网关gateway_url：https://openapi.alipaydev.com/gateway.do，线上网关gateway_url：https://openapi.alipay.com/gateway.do
    'sign_type'                => env('ALIPAY_SIGN_TYPE',"RSA2") ,
    'charset'                  => env('ALIPAY_CHARSET',"UTF-8") ,
    'notify_url' => 'http://alipay.app.test/api/alipay/notify',
    // 接口内容加密方式
    'aes_key' => env('ALIPAY_AES_KEY'),
    // 支付宝公钥
    'alipay_public_key'        => env('ALIPAY_PUBLIC_KEY'),
    //应用私钥
    'merchant_private_key'     => env('ALIPAY_MERCHANT_PRIVATE_KEY'),
];
