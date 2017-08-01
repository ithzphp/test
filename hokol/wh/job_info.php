<?php
require_once('/Api/Controller/Ucpaass.class.php');
$options['accountsid']='e270e220daacd078192ca279948232da';
$options['token']='029c66f311a1e38d39a75118adb6ab8c';


//初始化 $options必填
$ucpass = new Ucpaass($options);

//开发者账号信息查询默认为json或xml
$appId = "548b777057714ad693faf8bc5f0f970a";
$mobile = "13605800240";
echo $ucpass->getDevinfo('json');
//echo $ucpass->getClientInfoByMobile($appId,$mobile);
$date = "1";

echo $ucpass->getBillList($appId,$date);
//var_dump($rec);
