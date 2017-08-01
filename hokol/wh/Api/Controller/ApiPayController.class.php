<?php
namespace Api\Controller;
use 	  Think\Controller;
class ApiPayController extends Controller{
	public function alipay(){
		vendor('alipay.AopSdk');
		//$alipayconf = C("ALIPAY_CONF");
        /*生成用户订单号*/
        $user_id = $data['user_id'];//获取充值用户ID
        $user_trad = D("User_trad");
        $out_trade_no = $user_id.'-'.time();
        $arr['user_id'] = $user_id;
        $arr['out_trade_no'] = $out_trade_no;
        $arr['add_time'] = time();
        $res = $user_trad -> add($arr);
        /*生成订单*/
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";       
        $aop->appId = "2016080300158242";//实际上线app id需真实的
        $aop->rsaPrivateKey = '填写工具生成的商户应用私钥';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA";
        $aop->alipayrsaPublicKey = '填写从支付宝开放后台查看的支付宝公钥';
        $bizcontent = json_encode([
            'body'=>'红客文化',
            'subject'=>'红豆充值',
            'out_trade_no'=>$out_trade_no,//此订单号为商户唯一订单号
            'total_amount'=> '9.88',//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY'
        ]);
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //支付宝回调
        $request->setNotifyUrl("http://www.wh.com/index.php/Api/ApiPay/alipay_notify");
        //$request->setNotifyUrl("http://120.92.35.211/wanghong/wh/index.php/Api/ApiPay/alipay_notify");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        echo htmlspecialchars($response);
	}
	public function alipay_notify(){
		vendor('alipay.AopSdk');
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = '请填写支付宝公钥，一行字符串';
        //此处验签方式必须与下单时的签名方式一致
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA");
        //验签通过后再实现业务逻辑，比如修改订单表中的支付状态。
        /*
        验签通过后核实如下参数out_trade_no、total_amount、seller_id
        修改订单表
        */
        //打印success，应答支付宝。必须保证本界面无错误。只打印了success，否则支付宝将重复请求回调地址。
        dump($flag);
        echo 'success';
	}
    public function wechatpay(){

    }
    public function get_weixin_sign(){
        $appid = '';
        $mch_id = '';
        $body = '';
        $nocr_str = rand(155556,236598).time();
        dump($nocr_str);
        $key = '';
        $str = 'appid=';
    }
}