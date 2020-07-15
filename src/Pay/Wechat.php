<?php
namespace SuperPay\Pay;

use SuperPay;

class Wechat implements pay
{
    protected $param  = [];
    protected $mchid  = '';
    protected $payKey = '';
    protected $appid  = '';
    /**
     *  @param mch_appid        是
     *  @param appid            是 申请商户号的appid或商户号绑定的appid
     *  @param mchid            是 微信支付分配的商户号
     */
    public function __construct($param)
    {
        $this->mchid  = $param['mchid'];
        $this->payKey = $param['pay_key'];
        $this->appid  = $param['mch_appid'];
    }

	// 下单支付
	public function pay()
	{

	}
  

  	// 统一下单
  	public function unifiedorder()
  	{
  		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
  	}
   
}