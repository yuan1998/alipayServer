<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8" />
    <title>amis demo</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1"
    />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/amis@2.6.1-alpha.0/sdk/sdk.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/amis@2.6.1-alpha.0/sdk/helper.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/amis@2.6.1-alpha.0/sdk/iconfont.css" />
    <!-- 这是默认主题所需的，如果是其他主题则不需要 -->
    <!-- 从 1.1.0 开始 sdk.css 将不支持 IE 11，如果要支持 IE11 请引用这个 css，并把前面那个删了 -->
    <!-- <link rel="stylesheet" href="sdk-ie11.css" /> -->
    <!-- 不过 amis 开发团队几乎没测试过 IE 11 下的效果，所以可能有细节功能用不了，如果发现请报 issue -->
    <style>
        html,
        body,
        .app-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
<div id="root" class="app-wrapper"></div>
<script src="https://cdn.jsdelivr.net/npm/amis@2.6.1-alpha.0/sdk/sdk.js"></script>
<script type="text/javascript">
    (function () {
        let amis = amisRequire('amis/embed');
        // 通过替换下面这个配置来生成不同页面
        let amisJSON = {
            type: 'page',
            body: {
                title: '订单总额',
                type: 'form',
                api: '/api/alipay/create',
                body: [
                    {
                        type: 'hidden',
                        name: 'notify_url',
                        value: "{{$notifyUrl}}"
                    },
                    {
                        type: 'hidden',
                        name: 'notify_id',
                        value: "{{$notifyId}}"
                    },
                    {
                        type: 'hidden',
                        name: 'price',
                        value: "{{$price}}"
                    },

                    {
                        label: '个人版 - 循环 - {{$price}}GiB x 月付',
                        type: 'static',
                        value: "¥{{$price}}",
                        name: '_price'
                    },
                    {
                        type:'divider',
                    },
                    {
                        label: '支付手续费',
                        type: 'static',
                        name: 'fee_price',
                        value: "¥{{$feePrice}}"
                    },
                    {
                        type:'divider',
                    },
                    {
                        label: '总计',
                        type: 'static',
                        name: 'total',
                        value: "¥{{$feePrice + $price}}CNY"
                    },

                    {
                        "type": "static-qr-code",
                        "name": "pay_url",
                        "visibleOn": "typeof data.pay_url !== 'undefined'",
                        "label": "支付二维码"
                    },
                    {
                        "type": "static",
                        "name": "message",
                        "visibleOn": "typeof data.message !== 'undefined'",
                        "label": "返回信息"
                    },
                    {
                        "type": "static-json",
                        "name": "json",
                        "visibleOn": "typeof data.json !== 'undefined'",
                        "label": "支付进度信息"
                    }
                ],
                "actions": [
                    {
                        "type": "submit",
                        block: true,
                        level: 'primary',
                        "label": "支付宝下单"
                    },
                ]
            }
        };
        let amisScoped = amis.embed('#root', amisJSON);
    })();
</script>
</body>
</html>
