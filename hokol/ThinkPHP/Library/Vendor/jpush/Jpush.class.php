<?php
namespace Vendor\jpush;
use JPush\Client as Push;
class Jpush{
	  //极光推送appkey  
    static public function app_key(){  
  
        $app_key = "6f3dd20f73f6f8f3213493ae";  
        return $app_key;  
    } 
	//极光推送master_secret  
    static public function master_secret(){  
  
        $master_secret = "86311da2a50a292e3dbc6e84";  
        return $master_secret;  
    }  
    //获取alias和tags  
    public function getDevices($registrationID){   
  
        $app_key = $this->app_key();  
        $master_secret = $this->master_secret();  
  
        $client = new JPush($app_key, $master_secret);  
  
        $result = $client->device()->getDevices($registrationID);  
          
        return $result;  
  
    }  
    //添加tags  
    public function addTags($registrationID,$tags){  
  
        $app_key = $this->app_key();  
        $master_secret = $this->master_secret();  
  
        $client = new JPush($app_key, $master_secret);  
  
        $result = $client->device()->addTags($registrationID,$tags);  
          
        return $result;  
  
    }  
  
    //移除tags  
    public function removeTags($registrationID,$tags){   
  
        $app_key = $this->app_key();  
        $master_secret = $this->master_secret();  
  
        $client = new JPush($app_key, $master_secret);  
  
        $result = $client->device()->removeTags($registrationID,$tags);  
          
        return $result;  
  
    }  
    //标签推送  
    public function push_tag($tag,$alert){  
        $app_key = $this->app_key();  
        $master_secret = $this->master_secret();  
  
        $client = new JPush($app_key, $master_secret);  
  
        $tags = implode(",",$tag);  
  
        $client->push()  
                ->setPlatform(array('ios', 'android'))  
                ->addTag($tags)                          //标签  
                ->setNotificationAlert($alert)           //内容  
                ->send();  
  
    }  
  
    //别名推送  
    public function push_alias($alias,$alert){ 
  
        $app_key = $this->app_key();  
        $master_secret = $this->master_secret();  
  
        $client = new JPush($app_key, $master_secret);  
  
        $alias = implode(",",$alias);  
  
        $client->push()  
                ->setPlatform(array('ios', 'android'))  
                ->addAlias($alias)                      //别名  
                ->setNotificationAlert($alert)          //内容  
                ->send();  
  
    }  
}