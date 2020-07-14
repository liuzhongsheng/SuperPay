![mahua](mahua-logo.jpg)

##M 有哪些功能？

* 方便的`支付\转账`功能
    *  直接引入文件就可以使用，无需繁琐配置
* 接入微信企业转账功能:可实现转账到银行卡，余额


## 有问题反馈
在使用中有任何问题，欢迎反馈给我，可以用以下联系方式跟我交流

* 邮件(996674366#gmail.com, 把#换成@)
* 微信:zhongsheng510
* QQ:996674366

## 安装方法
### 一、直接下载
***
https://github.com/liuzhongsheng/SuperPay
***

### 二、使用composr安装
***
composer create-project liuzhongsheng/super-pay
***

### 使用说明
	1.当返回错误码为“SYSTEMERROR”时，请不要更换商户订单号，一定要使用原商户订单号重试，否则可能造成重复支付等资金风险。
	2.请商户在自身的系统中合理设置付款频次并做好并发控制，防范错付风险。
	3.证书放置路径为:/cert/apiclient_cert.pem,/cert/apiclient_key.pem

### 一、实例化
***
	$baseData = [
		'mch_appid' => '', //申请商户号的appid或商户号绑定的appid
		'mchid'     => '', //微信支付分配的商户号
		'pay_key'   => '', //微信支付key
	];
	$obj = new SuperPay\Init($baseData)
***


#### 二、微信转账到余额
***
	$data = [
		'class_type_name'  => 'TransferAccounts', // 操作类型：TransferAccounts 提现
		'class_name'       => 'Wechat', // 要调用的类名支持：Wechat
		'device_info'      => '', // 设备号,选填，微信支付分配的终端设备号
		'partner_trade_no' => '', // 订单号商户订单号，需保持唯一性(只能是字母或者数字，不能包含有其它字符)
		'openid'           => '', // 用户openid
		'check_name'       => '', // 校验用户姓名选项 NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名
		're_user_name'     => '', // 收款用户真实姓名。如果check_name设置为FORCE_CHECK，则必填用户真实姓名
		'amount'           => '', // 企业付款金额，单位为元
		'desc'             => '', // 企业付款备注，必填。注意：备注中的敏感词会被转成字符*

	];
    $obj->query($data);
***



#### 三、微信转账到银行卡
***
	1.生成pubKey.pem
	$res = $obj->query($data,'getPublicKey');
	file_put_contents('cert/pubkey.pem', $res['pub_key']);

	2.使用openssl转换格式，进入cert目录执行如下命令
	openssl rsa -RSAPublicKey_in -in pubkey.pem -pubout  生成后的文件名.pem   

	3.发起转账		   
	$data = [
		'class_type_name'  => 'TransferAccounts', // 操作类型：TransferAccounts 提现
		'class_name'       => 'Wechat', // 要调用的类名支持：Wechat
		'partner_trade_no' => '', // 订单号商户订单号，需保持唯一性(只能是字母或者数字，不能包含有其它字符)
		'enc_bank_no'      => '', // 收款方银行卡号
		'enc_true_name'    => '', // 收款方用户名
		'bank_code'        => '', // 银行卡所在开户行编号,详见
		'amount'           => '', // 企业付款金额，单位为元
		'desc'             => '', // 企业付款备注，必填。注意：备注中的敏感词会被转成字符*
	];

	$obj->query($data);
***
