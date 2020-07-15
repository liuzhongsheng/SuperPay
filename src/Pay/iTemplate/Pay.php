<?php
namespace SuperPay\Pay;
// 声明一个支付接口
interface Pay
{
    public function pay($param);
}
