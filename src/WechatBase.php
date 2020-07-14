<?php
namespace SuperPay;

class WechatBase
{

    protected static function postXmlCurl($xml, $url, $second = 30, $cert = false)
    {
        $isdir = "cert/"; //证书位置
        $ch    = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, $second); //设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url); //抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //
        if ($cert == true) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM'); //证书类型
            curl_setopt($ch, CURLOPT_SSLCERT, $isdir . 'apiclient_cert.pem'); //证书位置
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM'); //CURLOPT_SSLKEY中规定的私钥的加密类型
            curl_setopt($ch, CURLOPT_SSLKEY, $isdir . 'apiclient_key.pem'); //证书位置
            curl_setopt($ch, CURLOPT_CAINFO, 'PEM');
            curl_setopt($ch, CURLOPT_CAINFO, $isdir . 'rootca.pem');
        }
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); //全部数据使用HTTP协议中的"POST"操作来发送

        $data = curl_exec($ch); //执行回话
        if ($data) {
            curl_close($ch);
            return $data;
        }
        $error = curl_errno($ch);
        echo "call faild, errorCode:$error\n";
        curl_close($ch);
        return false;

    }

    //数组转换成 xml
    protected function arrayToXml($arr)
    {
        $xml = '<xml>';
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= '<' . $key . '>' . arrayToXml($val) . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '>' . $val . "</" . $key . '>';
            }
        }
        $xml .= '</xml>';
        return $xml;
    }

    //xml 转换成数组
    protected function xmlToArray($xml)
    {
        //禁止引用外部 xml 实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val       = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    //作用：格式化参数，签名过程需要使用
    protected function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = '';
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . '=' . $v . '&';
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
    //作用：生成签名
    protected function getSign($Obj, $pay_key = '')
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在 string 后加入 KEY
        $String = $String . '&key=' . $pay_key;
        //签名步骤三：MD5 加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $String;
    }
}
