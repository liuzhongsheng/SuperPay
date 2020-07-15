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

###功能列表
1. [提现转账](https://github.com/liuzhongsheng/SuperPay#提现转账 "提现转账"):[微信转账到银行卡](https://github.com/liuzhongsheng/SuperPay#微信转账到银行卡 "微信转账到银行卡")，[微信转账到余额](https://github.com/liuzhongsheng/SuperPay#微信转账到余额 "微信转账到余额")
2. [微信支付](https://github.com/liuzhongsheng/SuperPay#微信支付 "微信支付")

## 安装方法
### 一、直接下载
***
https://github.com/liuzhongsheng/SuperPay
***

### 二、使用composr安装
***
composer create-project liuzhongsheng/super-pay
***
## 提现转账
### 一、转账使用说明
	1.当返回错误码为“SYSTEMERROR”时，请不要更换商户订单号，一定要使用原商户订单号重试，否则可能造成重复支付等资金风险。
	2.请商户在自身的系统中合理设置付款频次并做好并发控制，防范错付风险。
	3.证书放置路径为:/cert/apiclient_cert.pem,/cert/apiclient_key.pem
	4.案例请参考:/test/TransferAccounts.php
### 二、实例化

	$baseData = [
		'mch_appid' => '', //申请商户号的appid或商户号绑定的appid
		'mchid'     => '', //微信支付分配的商户号
		'pay_key'   => '', //微信支付key
	];
	$obj = new SuperPay\Init($baseData)



#### 三、微信转账到余额

	$data = [
		'class_type_name'  => 'TransferAccounts', // 操作类型：TransferAccounts 提现 Pay 支付
		'class_name'       => 'Wechat', // 要调用的类名支持：Wechat
		'device_info'      => '', // 设备号,选填，微信支付分配的终端设备号
		'partner_trade_no' => '', // 订单号商户订单号，需保持唯一性(只能是字母或者数字，不能包含有其它字符)
		'openid'           => '', // 用户openid
		'check_name'       => '', // 校验用户姓名选项 NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名
		//'re_user_name'     => '', // 收款用户真实姓名。如果check_name设置为FORCE_CHECK，则必填用户真实姓名
		'amount'           => '', // 企业付款金额，单位为元
		'desc'             => '', // 企业付款备注，必填。注意：备注中的敏感词会被转成字符*

	];
    $obj->query($data);


#### 四、微信转账到银行卡

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

## 微信支付
### 一、实例化

	$baseData = [
		'appid' => '', //申请商户号的appid或商户号绑定的appid
		'mchid'     => '', //微信支付分配的商户号
		'pay_key'   => '', //微信支付key
	];
	$obj = new SuperPay\Init($baseData)

#### 二、微信支付
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

	$obj->query($data, 'pay');
#### 三、获取回调信息
	$data = [
	    // 以下选项为必填项
	    'class_type_name' => 'Pay', // 操作类型：TransferAccounts 提现 Pay 支付
	    'class_name'      => 'Wechat', // 要调用的类名支持：Wechat
	];
	$obj->query($data,'notify');

#### 四、发送模板消息(开发中)