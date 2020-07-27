<?php
namespace SuperPay\Pay;

use SuperPay;

class Wechat extends SuperPay\WechatBase implements Pay, Notify
{
    protected $param     = [];
    protected $mchid     = '';
    protected $payKey    = '';
    protected $appid     = '';
    protected $app_secret = '';
    /**
     *  @param appid            是 申请商户号的appid或商户号绑定的appid
     *  @param mchid            是 微信支付分配的商户号
     */
    public function __construct($param = [])
    {
        if (!empty($param)) {
            $this->mchid     = $param['mchid'];
            $this->payKey    = $param['pay_key'];
            $this->appid     = $param['appid'];
            $this->app_secret = $param['app_secret'];
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
        $this->param               = $param;
        $data                      = $this->unifiedorder();

        $parameters = array(
            'appId'     => $param['appid'], //小程序 ID
            'timeStamp' => '' . time() . '', //时间戳
            'nonceStr'  => md5($param['nonce_str']), //随机串
            'package'   => 'prepay_id=' . $data['prepay_id'], //数据包
            'signType'  => $param['sign_type'], //签名方式
        );
        //签名
        $parameters['paySign']     = $this->getSign($parameters, $this->payKey);
        $parameters['return_code'] = $data['return_code'];
        if ($data['return_code'] == 'SUCCESS') {
            $parameters['result_code']  = $data['result_code'];
            $parameters['err_code']     = $data['err_code'];
            $parameters['err_code_des'] = $data['err_code_des'];
        } else {
            $parameters['result_code']  = '';
            $parameters['err_code']     = '';
            $parameters['err_code_des'] = '';
        }
        unset($parameters['appid']);
        return $parameters;
    }
    // 发送消息模板
    public function sendMessage($param)
    {
        $token = $this->getAccessToken();
        //跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版
        if (!array_key_exists('miniprogram_state', $param)) {
            $param['miniprogram_state'] = 'formal';
        }

        //进入小程序查看”的语言类型，支持zh_CN(简体中文)、en_US(英文)、zh_HK(繁体中文)、zh_TW(繁体中文)，默认为zh_CN
        if (!array_key_exists('lang', $param)) {
            $param['lang'] = 'zh_CN';
        }

        //点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转
        if (!array_key_exists('page', $param)) {
            $param['page'] = '';
        }
        $data = [
            //接收者（用户）的 openid
            'touser'            => $param['touser'],
            //所需下发的订阅模板id
            'template_id'       => $param['template_id'],
            'page'              => $param['page'],
            'data'              => $param['data'],
            'miniprogram_state' => $param['miniprogram_state'],
            'lang'              => $param['lang'],
        ];

        $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $token;
        $re  = $this->postXmlCurl(json_encode($data), $url, 60, false);
        return $re;
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

    // 获取access_token
    protected function getAccessToken()
    {
        $url  = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->app_secret;
        $r    = file_get_contents($url); //返回的是字符串，需要用json_decode转换成数组
        $data = json_decode($r, true);
        return $data['access_token'];
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
