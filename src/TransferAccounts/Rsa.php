<?php
namespace SuperPay\TransferAccounts;

/*
 * 黎明互联
 * https://www.liminghulian.com/
 */

class Rsa
{

    private $public_key_resource  = ''; //公钥资源
    private $private_key_resource = ''; //私钥资源
    /**
     * 构造函数
     * @param [string] $public_key  [公钥数据字符串]
     * @param [string] $private_key [私钥数据字符串]
     */
    public function __construct($public_key, $private_key)
    {
        $this->public_key_resource  = !empty($public_key) ? openssl_pkey_get_public($this->get_public_key($public_key)) : false;
        $this->private_key_resource = !empty($private_key) ? openssl_pkey_get_private($this->get_private_key($private_key)) : false;
    }

    /**
    获取私有key字符串 重新格式化  为保证任何key都可以识别
     */
    public function get_private_key($private_key)
    {
        $search = [
            "-----BEGIN RSA PRIVATE KEY-----",
            "-----END RSA PRIVATE KEY-----",
            "\n",
            "\r",
            "\r\n",
        ];

        $private_key = str_replace($search, "", $private_key);
        return $search[0] . PHP_EOL . wordwrap($private_key, 64, "\n", true) . PHP_EOL . $search[1];
    }

    /**

    获取公共key字符串  重新格式化 为保证任何key都可以识别
     */

    public function get_public_key($public_key)
    {
        $search = [
            "-----BEGIN PUBLIC KEY-----",
            "-----END PUBLIC KEY-----",
            "\n",
            "\r",
            "\r\n",
        ];
        $public_key = str_replace($search, "", $public_key);
        return $search[0] . PHP_EOL . wordwrap($public_key, 64, "\n", true) . PHP_EOL . $search[1];
    }

    /**
     * 生成一对公私钥 成功返回 公私钥数组 失败 返回 false
     */
    public function create_key()
    {
        $res = openssl_pkey_new();
        if ($res == false) {
            return false;
        }

        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res);
        return array('public_key' => $public_key["key"], 'private_key' => $private_key);
    }
    /**
     * 用私钥加密
     */
    public function private_encrypt($input)
    {
        openssl_private_encrypt($input, $output, $this->private_key_resource);
        return base64_encode($output);
    }
    /**
     * 解密 私钥加密后的密文
     */
    public function public_decrypt($input)
    {
        openssl_public_decrypt(base64_decode($input), $output, $this->public_key_resource);
        return $output;
    }
    /**
     * 用公钥加密
     */
    public function public_encrypt($input)
    {
        openssl_public_encrypt($input, $output, $this->public_key_resource, OPENSSL_PKCS1_OAEP_PADDING);
        return base64_encode($output);
    }
    /**
     * 解密 公钥加密后的密文
     */
    public function private_decrypt($input)
    {
        openssl_private_decrypt(base64_decode($input), $output, $this->private_key_resource, OPENSSL_PKCS1_OAEP_PADDING);
        return $output;
    }
}
