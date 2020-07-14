<?php
namespace SuperPay\TransferAccounts;

use SuperPay;

class Wechat extends SuperPay\WechatBase implements Commit
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

    /**
     *  @param device_info      否 微信支付分配的终端设备号
     *  @param partner_trade_no 是 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有其它字符)
     *  @param openid           是 商户appid下，某用户的openid
     *  @param check_name       是 NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名
     *  @param re_user_name     否 收款用户真实姓名。如果check_name设置为FORCE_CHECK，则必填用户真实姓名
     *  @param amount           是 企业付款金额，单位为分
     *  @param desc             是 企业付款备注，必填。注意：备注中的敏感词会被转成字符*
     */
    public function commit($param)
    {
        if (!array_key_exists('openid', $param)) {
            return $this->bank($param);
        }
        return $this->surplus($param);
    }
    // 转账到余额
    protected function surplus($param)
    {
        $url                       = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $param['spbill_create_ip'] = $_SERVER['SERVER_ADDR'];
        $param['amount']           = $param['amount'] * 100;
        $param['nonce_str']        = md5(time() . mt_rand(1, 9999999));
        $param['mch_appid']        = $this->appid;
        $param['mchid']            = $this->mchid;
        $this->param               = $param;
        return $this->send($url);
    }

    // 转账到银行卡
    protected function bank($param)
    {
        $url         = 'https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank';
        $rsa         = new Rsa(file_get_contents('cert/NewPubKey.pem'), '');
        $this->param = [
            'mch_id'           => $this->mchid, //商户号
            'partner_trade_no' => $param['partner_trade_no'], //商户付款单号
            'nonce_str'        => md5(time() . mt_rand(1, 9999999)), //随机串
            'enc_bank_no'      => $rsa->public_encrypt($param['enc_bank_no']), //收款方银行卡号RSA加密
            'enc_true_name'    => $rsa->public_encrypt($param['enc_true_name']), //收款方姓名RSA加密
            'bank_code'        => $param['bank_code'], //收款方开户行
            'amount'           => $param['amount'] * 100, //付款金额
        ];
        return $this->send($url);

    }
    public function getPublicKey($param = null)
    {
        $url         = 'https://fraud.mch.weixin.qq.com/risk/getpublickey';
        $this->param = [
            'mch_id'    => $this->mchid, //商户ID
            'nonce_str' => md5(time()),
            'sign_type' => 'MD5',
        ];
        //将数据发送到接口地址
        return $this->send($url);
    }
    protected function send($url)
    {
        $this->param['sign'] = $this->getSign($this->param, $this->payKey);
        $xml                 = $this->arrayToXml($this->param);
        $result              = $this->postXmlCurl($xml, $url, 60, true);
        return $this->xmlToArray($result);
    }
}
