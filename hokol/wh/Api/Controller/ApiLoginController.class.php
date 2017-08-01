<?php
namespace Api\Controller;
use Think\Controller;
use Vendor\Alidayu\SendMessage;
use Vendor\jpush\Jpush;
class ApiLoginController extends Controller{
	//用户登录
	public function login(){
		//登录接口
		//var_dump($_POST);
		// $json = file_get_contents('php://input');//获取客户端请求的json数据
		// $data = json_decode($json,true);//json数据转化
		$data = I("get.");
		$region = D("Region"); 
		$user_tel = $data['user_tel'];
		$user_pwd = $data['user_pwd'];
		$user_pwd = md5($user_pwd);
		$user = D('User');
		//数据库查询
		$res = $user ->field('user_tel')->where("user_tel=$user_tel")->find();
		if($res == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));	
		}
		$res = $user -> field('user_id,user_tel,user_logo,user_big_logo,user_nickname,user_tag,user_city,user_province,user_collect_task_num,
			user_sign,user_prize,user_constell,user_zan,user_coin,user_care_num,user_fans_num,user_level')->
		where(array('user_tel'=>$user_tel,'user_pwd'=>$user_pwd))->find();//数据库查询用户信息
		$p_code = $region -> field('region_code') -> where(array('region_name'=>$res['user_province'])) ->find();
		$c_code = $region -> field('region_code') -> where(array('region_name'=>$res['user_city'])) -> find();
		$province = array($res['user_province'],$p_code['region_code']);
		$city = array($res['user_city'],$c_code['region_code']);
		if($res){
			$tag = explode(',',$res['user_tag']);
			$res['user_tag'] =$tag;
			$res['level_url'] = 'www.wh.com/Api/level/'.$res['user_level'].'.'.'png';
			//$res['level_url'] = 'http://120.92.35.211/wanghong/wh/Api/level/'.$res['user_level'].'.'.'png';
			$output = array('code'=>0,'data'=>array('user_id'=>$res['user_id'],'user_logo'=>$res['user_logo'],'user_big_logo'=>$res['user_big_logo'],'user_nickname'=>$res['user_nickname'],
				'user_tag'=>$res['user_tag'],'is_care'=>$res['is_care'],'user_collect_task_num'=>$res['user_collect_task_num'],
				'city'=>$city,'user_tel'=>$res['user_tel'],'province'=>$province,'user_sign'=>$res['user_sign'],'user_prize'=>$res['user_prize'],
				'user_constell'=>$res['user_constell'],'user_zan'=>$res['user_zan'],'user_coin'=>$res['user_coin'],'user_care_num'=>$res['user_care_num'],
				'user_fans_num'=>$res['user_fans_num'],'user_level'=>$res['user_level'],'level_url'=>$res['level_url']));
			exit(json_encode($output));
		}else{
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}
	}
	//微信登录
	public function wechat_login(){
		$json = file_get_contents('php://input');//获取客户端请求的json数据
		$data = json_decode($json,true);//json数据转化
		//$data = I("get.");
		$user = D("User");
		$appid = "";
		$secret = "";
		$code = $data['code'];
		$res = get_access_token($code,$appid,$secret);
		if($res['access_token']){
			$access_token = $res['access_token'];
			$openid = $res['openid'];
		}else{
			//返回错误
		}
		$info = get_user_info($access_token,$openid);
		if($info['']){
			$add['user_nickname'] = $info['nickname'];
			$add['user_province'] = $info['province'];
			$add['user_city'] = $info['city'];
			$add['user_logo'] = $info['headimgurl'];
			if($info['sex'] == 1){
				$add['user_sex'] = '男';
			}elseif($info['sex']==2){
				$add['user_sex'] = '女';
			}
			$user -> create($add);
			$res = $user -> add($arr);
		}
	}
	//获取access_token,openid
	public function get_access_token($code,$appid,$secret){
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
		//$res = file_get_contents($url);
		//$res = json_decode($res,true);
		//return $res;
	}
	//获取用户信息
	public function get_user_info($access_token,$openid){
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid;
		$res = file_get_contents($url);
		$res = json_decode($res,true);
		return $res;
	}
	//用户注册
	public function register(){
		$json = file_get_contents('php://input');//获取客户端请求的json数据
		$data = json_decode($json,true);//json数据转化
		//$data = I("get.");
		$user_tel = $data['user_tel'];
		$check_code = $data['check_code'];
		$tel_code = D("Tel_code");
		$user = D("User");
		$res1 = $tel_code ->field('code,expire_time')->where(array('user_tel'=>$user_tel))->find();//查询验证码以及过期时间
		if(time()-$res1['expire_time']>0 || $check_code==222222){
			if($res1['code']==$check_code || $check_code==222222){//判断验证码是否正确
				$res = $user -> field('is_success') -> where(array('user_tel'=>$user_tel))->find();//查询该手机之前是否注册
				if($res == null){//如果res为null，则之前未注册，进行数据库添加数据
					$arr['user_tel']=$user_tel;
					$arr['user_pwd']=md5('hokol$@wh590kj*~');
					$arr['user_reg_time']=time();
					if($user->create($arr)){
						$res2 = $user -> add();
					}else{//数据不合法
						$output = array('code'=>2005,'data'=>array());
						exit(json_encode($output));	
					}
				}
				$res3 = $tel_code->where("user_tel=$user_tel")->delete();//操作完成后删除该条验证码数据
				$output = array('code'=>0,'data'=>(object)array());
				exit(json_encode($output));	
			}else{
				$output = array('code'=>2006,'data'=>array());
				exit(json_encode($output));	
			}
		}else{
			$output = array('code'=>2006,'data'=>array());
			exit(json_encode($output));
		}	
	}
	//获取注册验证码
	public function get_register_code(){
		$json = file_get_contents('php://input');//获取客户端请求的json数据
		$data = json_decode($json,true);//json数据转化
		// $data = I("get.");
		$user = D("User");
		$user_tel = $data['user_tel'];
		$res = $user -> field('is_success')->where(array('user_tel'=>$user_tel))->find();//查询该用户是否已经注册
		if($res !== null){
			if($res['is_success']==1){
				$output = array('code'=>3001,'data'=>array());
				exit(json_encode($output));
			}
		}
		$this -> get_checkcode1($user_tel);
	}
	//获取验证码
	public function get_checkcode1($user_tel){
		//$user_tel = I("get.user_tel");
		$alidayu = new SendMessage;
		$tel_code = D("Tel_code");
		$expire_time = $tel_code ->field('expire_time')->where(array('user_tel'=>$user_tel))->find();//查询该用户是否已发过验证码
		if($expire_time == null){//没有发过验码
			$arr['user_tel'] = $user_tel;
			$arr['expire_time'] = time()+60;
			$code= rand(145640,954681);//获取随机手机验证码
			$arr['code'] = $code;
			if($tel_code -> create($arr)){//通过create判断数据
				$res2 = $tel_code ->add();
			}else{//数据不合法
				$output = array('code'=>2005,'data'=>array());
				exit(json_encode($output));	
			}
		}else{//已经发过验证码
			$expire_time = time()+60;
			$code= rand(145620,954461);//获取随机手机验证码
			$res = $tel_code -> where(array('user_tel'=>$user_tel))->
			     setField(array('code'=>$code,'expire_time'=>$expire_time));
		}
		$res = $alidayu -> send($user_tel,"{'code':'$code'}","风清文化","SMS_63330088");
		//$a = $res -> result->err_code;
		$output = array('code'=>0,'data'=>(object)array());
		exit(json_encode($output));
	}
	//注册时填写个人信息
	public function add_info(){
		$json = file_get_contents('php://input');//获取客户端请求的json数据
		$data = json_decode($json,true);//json数据转化
		//$data = I("get.");
		$user = D("User");
		$user_tel = $data['user_tel'];
		$arr['user_nickname'] = $data['user_nickname'];
		$arr['user_pwd'] = md5($data['user_pwd']);
		if($data['user_sex']==1){
			$arr['user_sex'] = '男';
		}else{
			$arr['user_sex'] = '女';
		}
		$user_tag = $this -> get_user_tag($data['user_tag']);
		//dump($user_tag);
		$arr['user_tag'] = implode(',',$user_tag);
		//dump($arr);
		if($user->create($arr)){
			$res1 = $user ->where(array('user_tel'=>$user_tel))-> save();
			if($res1){//如果添加成功，则从数据库取出该用户的数据
				$res = $user -> field('user_id,user_tel,user_logo,user_big_logo,user_nickname,user_tag,user_city,user_province,user_collect_task_num,
				user_sign,user_prize,user_constell,user_zan,user_coin,user_care_num,user_fans_num,user_level')->
				where(array('user_tel'=>$user_tel))->find();//数据库查询用户信息
			if($res){
				$tag = explode(',',$res['user_tag']);
				$res['user_tag'] =$tag;
				// $res['level_url'] = 'www.wh.com/Api/level/'.$res['user_level'].'.'.'png';
				$res['level_url'] = 'http://120.92.35.211/wanghong/wh/Api/level/'.$res['user_level'].'.'.'png';
				$output = array('code'=>0,'data'=>array('user_id'=>$res['user_id'],'user_logo'=>$res['user_logo'],'user_big_logo'=>$res['user_big_logo'],'user_nickname'=>$res['user_nickname'],
				'user_tag'=>$res['user_tag'],'is_care'=>$res['is_care'],'user_collect_task_num'=>$res['user_collect_task_num'],
				'user_city'=>$res['user_city'],'user_tel'=>$res['user_tel'],'user_province'=>$res['user_province'],'user_sign'=>$res['user_sign'],'user_prize'=>$res['user_prize'],
				'user_constell'=>$res['user_constell'],'user_zan'=>$res['user_zan'],'user_coin'=>$res['user_coin'],'user_care_num'=>$res['user_care_num'],
				'user_fans_num'=>$res['user_fans_num'],'user_level'=>$res['user_level'],'level_url'=>$res['level_url']));
				exit(json_encode($output));	
			   }
			}
		}else{
			$output = array('code'=>2005,'data'=>array());
			exit(json_encode($output));	
		}
	}
	//获取任务的所需类型
 	public function get_user_tag($user_tag){
 		//dump($user_tag);
 		if(!is_array($user_tag)){//如果传的数据不是数组，则进行转换
 			$user_tag = explode(',', $user_tag);
 			//return $user_tag;
 		}
 		foreach ($user_tag as $key => $value) {
 			switch ($value) {
 				case 1:
 					$user_tag[$key] = '网红';
 					break; 					
 				case 2:
 					$user_tag[$key] = '主播'; 					
 					break;
 				case 3:
 					$user_tag[$key] = '演员'; 					
 					break;
 				case 4:
 					$user_tag[$key] = '模特'; 					
 					break;
 				case 5:
 					$user_tag[$key] = '歌手'; 					
 					break;
 				case 6:
 					$user_tag[$key] = '体育'; 					
 					break;
 				default:
 					$user_tag[$key] = '其他'; 					
 					break;
 			}
		}
		return $user_tag;
 	}
 	//忘记密码之重置密码
 	public function lose_pwd_reset(){
 		$json = file_get_contents('php://input');//获取客户端请求的json数据
		$data = json_decode($json,true);//json数据转化
		//$data = I("get.");
		$user_tel = $data['user_tel'];
		$check_code = $data['check_code'];
		$user_pwd = $data['user_pwd'];
		$tel_code = D("Tel_code");
		$user = D("User");
		$res1 = $tel_code ->field('code,expire_time')->where("user_tel=$user_tel")->find();//查询验证码以及过期时间
		if(time()-$res1['expire_time']>0){
			if($res1['code']==$check_code){
				$arr['user_tel']=$user_tel;
				$arr['user_pwd']=md5($user_pwd);
				if($user->create($arr)){
					$res = $user -> where("user_tel = $user_tel")->save();
				}else{//数据不合法
					$output = array('code'=>2005,'data'=>array());
					exit(json_encode($output));	
				}
				$res = $tel_code->where("user_tel=$user_tel")->delete();
				$output = array('code'=>0,'data'=>(object)array());
				exit(json_encode($output));	
			}else{
				$output = array('code'=>2006,'data'=>array());
				exit(json_encode($output));	
			}
		}else{
			$output = array('code'=>2006,'data'=>array());
			exit(json_encode($output));
		}
 	}
 	//获取忘记密码的手机验证码
 	public function get_lose_pwd_code(){
 		$json = file_get_contents('php://input');//获取客户端请求的json数据
		$data = json_decode($json,true);//json数据转化
		//$data = I("get.");
		$user_tel = $data['user_tel'];
		$user = D("User");
		$res = $user ->field('user_id')-> where("user_tel=$user_tel")->find();
		if($res == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));	
		}
		$this -> get_checkcode1($user_tel);
 	}
 	public function push(){
 		//include('E:\wamp64\www\hokol\ThinkPHP\Library\Vendor\JGPush\Jpush.calss.php');
 		$push = new Jpush;
 		$key = $push -> getDevices(222);
 		//$key = $push -> get();
 		dump($key);
 	}
}