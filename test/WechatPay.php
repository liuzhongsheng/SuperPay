<?php
// Autoload 自动载入
require '../vendor/autoload.php';

$baseData = [
    'appid'   => '', //申请商户号的appid或商户号绑定的appid
    'mchid'   => '', //微信支付分配的商户号
    'pay_key' => '', //微信支付key
];
$obj = new SuperPay\Init($baseData);

// 微信转账，到余额
$data = [
    // 以下选项为必填项
    'class_type_name' => 'Pay', // 操作类型：TransferAccounts 提现 Pay 支付
    'class_name'      => 'Wechat', // 要调用的类名支持：Wechat
    'out_trade_no'    => 'O' . time() . mt_rand(100, 999), //商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|*且在同一个商户号下唯一
    'total_fee'       => '1.0', //订单总金额，单位为元
    'body'            => '腾讯充值中心-QQ会员充值', //商品简单描述，该字段请按照规范传递
    'notify_url'      => 'https://api.apiself.com/v1/order/pay/notify', //异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。
    'trade_type'      => 'JSAPI', //支持JSAPI支付（或小程序支付）、NATIVE--Native支付、APP--app支付，MWEB--H5支付，不同trade_type决定了调起支付的方式，请根据支付产品正确上传
    'openid'          => 'o-AWq5SRFK3d3oZ7d5kQWlpxE4AY', //trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识
    // 其他选填项可根据自己实际需要添加，参数名需和小程序文档上一致
];

// $obj->query($data, 'pay');
$data = [
    // 以下选项为必填项
    'class_type_name' => 'Pay', // 操作类型：TransferAccounts 提现 Pay 支付
    'class_name'      => 'Wechat', // 要调用的类名支持：Wechat
];
// 查询回调信息

$obj->query($data,'notify');
