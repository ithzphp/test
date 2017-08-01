<?php
namespace Vendor\Alidayu;
include('TopSdk.php');
use TopClient;
use AlibabaAliqinFcSmsNumSendRequest;

class SendMessage{
	public function send($recNum,$code,$smsFreeSignName,$smsTemplateCode){
		$c = new TopClient;
		$c ->appkey = "23770998" ;
		$c ->secretKey = 'f8f3866571a4e0a8075a263db60333dc' ;
		$req = new AlibabaAliqinFcSmsNumSendRequest;
		$req ->setExtend( "123456" );
		$req ->setSmsType( "normal" );
		$req ->setSmsFreeSignName($smsFreeSignName);
		$req ->setSmsParam($code);
		$req ->setRecNum($recNum);
		$req ->setSmsTemplateCode($smsTemplateCode);
		$resp = $c ->execute( $req );
		return $resp;
	}
}