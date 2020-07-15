<?php
namespace SuperPay\Pay;

use SuperPay;

class Wechat extends SuperPay\WechatBase implements Pay, Notify
{
    protected $param  = [];
    protected $mchid  = '';
    protected $payKey = '';
    protected $appid  = '';
    /**
     *  @param appid            是 申请商户号的appid或商户号绑定的appid
     *  @param mchid            是 微信支付分配的商户号
     */
    public function __construct($param = [])
    {
        if (!empty($param)) {
            $this->mchid  = $param['mchid'];
            $this->payKey = $param['pay_key'];
            $this->appid  = $param['appid'];
        }
    }

    // 下单支付
    public function pay($param)
    {
        $param['appid']     = $this->appid;
        $param['mch_id']    = $this->mchid;
        $param['nonce_str'] = md5(time() . mt_rand(1, 999999999));
        if (array_key_exists('sign_type', $param) && $param['sign_type'] == 'HMAC-SHA256') {
            $param['sign_type'] = 'HMAC-SHA256';
        } else {
            $param['sign_type'] = 'MD5'; //MD5 签名类型，默认为MD5，支持HMAC-SHA256和MD5。
        }
        $param['total_fee']        = $param['total_fee'] * 100;
        $param['spbill_create_ip'] = $_SERVER['SERVER_ADDR'];

        $this->param = $param;
        return $this->unifiedorder();
    }

    // 回调通知
    public function notify($param = [])
    {
        //获取返回的xml
        $testxml = file_get_contents("php://input");
        //将xml转化为json格式
        $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
        //转成数组
        $result = json_decode($jsonxml, true);
        if ($result) {
            //如果成功返回了
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                //进行改变订单状态等操作。。。。
                return $result;
            }
        }
        return false;
    }
    
    // 统一下单
    public function unifiedorder()
    {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        return $this->send($url);
    }
    protected function send($url)
    {
        $this->param['sign'] = $this->getSign($this->param, $this->payKey);
        $xml                 = $this->arrayToXml($this->param);
        $result              = $this->postXmlCurl($xml, $url, 60, false);
        return $this->xmlToArray($result);
    }
}
