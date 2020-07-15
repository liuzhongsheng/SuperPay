<?php
namespace SuperPay\Pay;
// 声明一个回调通知接口
interface Notify
{
    public function notify($param);
}
