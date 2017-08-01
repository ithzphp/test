<?php
namespace Api\Controller;
use Think\Controller;
use Lib\Alidayu\SendMessage;
class ApiSettingController extends Controller{

	//用户意见反馈
	public function user_advice(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		// if(IS_POST){
		$data = I('post.');
		$user= D("User");
		$advice_user_id = $data['advice_user_id'];
		$res = $user -> field('user_id')->find($advice_user_id);
		if($res == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		$type = $data['advice_type'];
		switch ($type) {
			case 1:
			$data['advice_type'] = '应用崩溃';
			break;
			case 2:
			$data['advice_type'] = '功能问题';
			break;
			case 3:
			$data['advice_type'] = '注册问题';
			break;
			case 4:
			$data['advice_type'] = '订单支付';
			break;
			default:
			$data['advice_type'] = '改善建议';
			break;
		}
		$advice = D('User_advice');
		$rules = array(array('advice_time','time','1','function'));
		$advice -> auto($rules)->create($data);
		$res = $advice -> add();
		if($res){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
		// 	}
		// else{
		// 		$this ->display();
		// 	}
	}
	//重置密码
	public function reset_pwd(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		 //if(IS_POST){
			// $data = $_POST['data'];
			// dump($data);
			$old_pwd = $data['old_pwd'];
			$new_pwd = $data['new_pwd'];
			$user_id = $data['user_id'];
			$user = D('User');
			$res = $user -> field('user_pwd')->where("user_id=$user_id")->find();//查询该用户的密码
			if($res == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
			}
			$user_pwd = $res['user_pwd'];
			if($old_pwd == $user_pwd){//判断用户输的原密码是否正确
				$res = $user ->where("user_id=$user_id")->setField('user_pwd',$new_pwd);//更新数据
				if($res || $res==0){
					$output = array('code'=>0,'data'=>(object)array());
					exit(json_encode($output));
				}
			}else{//如果用户输入的原密码不正确，则返回错误
				$output = array('code'=>3000,'data'=>array());
				exit(json_encode($output));
			}
		// }else{
		// 	$this -> display();
		// }
	}
	//更换手机号
	public function reset_tel(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];
		$user_tel = $data['user_tel'];//获取新号码
		$check_code = $data['check_code'];
		$tel_code = D("Tel_code");
		$user = D("User");
		$res1 = $tel_code ->field('code,expire_time')->where("user_tel=$user_tel")->find();//查询验证码以及过期时间
		//dump($code);
		if(time()-$res1['expire_time']>0){
			if($res1['code']==$check_code){
				$arr['user_tel']=$user_tel;
				$arr['user_id']=$user_id;
				if($user->create($arr)){
					$res = $user -> save();
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
	//更换手机号获取验证码
	public function get_reset_tel_code(){
		$json = file_get_contents('php://input');//获取客户端请求的json数据
		$data = json_decode($json,true);//json数据转化
		//$data = I("get.");
		$user = D("User");
		$alidayu = new SendMessage;
		$tel_code = D("Tel_code");
		$user_tel = $data['user_tel'];
		$res = $user -> field('user_id')->where(array('user_tel'=>$user_tel))->find();//查询该用户是否已经注册
		//dump($res);
		if($res !== null){
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}
		$expire_time = $tel_code ->field('expire_time')->where(array('user_tel'=>$user_tel))->find();//查询该用户是否已发过验证码
		//dump($expire_time);
		if($expire_time == null){//没有发过验码
			$arr['user_tel'] = $user_tel;
			$arr['expire_time'] = time()+70;
			$code= rand(143560,955461);//获取随机手机验证码
			$arr['code'] = $code;
			if($tel_code -> create($arr)){//通过create判断数据
				$res2 = $tel_code ->add();
			}else{//数据不合法
				$output = array('code'=>2005,'data'=>array());
				exit(json_encode($output));	
			}
		}else{//已经发过验证码
			$expire_time = time()+70;
			$code= rand(145630,954561);//获取随机手机验证码
			$res = $tel_code -> where(array('user_tel'=>$user_tel))->
			     setField(array('code'=>$code,'expire_time'=>$expire_time));
			//dump($res);
		}
		$res = $alidayu -> send($user_tel,"{'code':'$code'}","风清文化","SMS_63330088");
		//dump($res);
		$output = array('code'=>0,'data'=>(object)array());
		exit(json_encode($output));
	}
	//修改用户logo
	public function reset_user_logo(){
		//if(IS_POST){
			//dump($_FILES);
		if($_FILES['user_logo']['error']==0){
			$user = D('User');
			$user_id = $_POST['user_id'];
			$res = $user -> field('user_logo,user_big_logo')->find($user_id);
			if($res == null){
				$output = array('code'=>2001,'data'=>array());
				exit(json_encode($output));
			}elseif (!empty($res['user_logo']) || !empty($res['user_big_logo'])) {
				unlink($res['user_logo']);
				unlink($res['user_big_logo']);
			}
			$cfg = array(
				'rootPath' => './Common/Uploads/user_logo/',
			);//给出存放根目录
			$up = new \Think\Upload($cfg);//new 上传类
			$z = $up -> uploadOne($_FILES['user_logo']);
			if($z){//如果文件上传成功，进行存储设置
				$user_big_logo = $up ->rootPath.$z['savepath'].$z['savename'];//文件初始存储地址
				$site = 'www.wh.com';//定义服务器地址
				//$site = 'http://120.92.35.211/wanghong/wh';
				$user_big_logo1 = $site.substr($user_big_logo,1);//拼接加上服务器的URL，存入数据库			
				$data['user_big_logo'] = $user_big_logo1;
				//制作缩略图
				$im = new \Think\Image();
          		$im -> open($user_big_logo); //打开原图
           		$im -> thumb(60,60); //制作缩略图
            	//缩略图名字：“small_原图名字”
          		$user_logo = $up->rootPath.$z['savepath']."small_".$z['savename'];//给出文件的地址
            	$im -> save($user_logo);//存储缩略图到服务器
            	$user_logo1 = $site.substr($user_logo,1);//拼接加上服务器的URL，存入数据库
				$data['user_logo'] = $user_logo1;
				$user -> create($data);
				$res = $user ->where("user_id = $user_id")->save($data);
				if($res || $res==0){
					$output = array('code'=>0,'data'=>(object)array());
					exit(json_encode($output));
				}
			}
		}else{
			$output = array('code'=>2004,'data'=>array());
			exit(json_encode($output));
		}
		// }else{
		// 	$this ->display();
		// }
	}
	//修改个人信息
	public function reset_user_info(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		//$data =I('get.');
		$user_id = $data['user_id'];
		$user_nickname = $data['user_nickname'];
		$user_sex = $data['user_sex'];
		$user_constell = $data['user_constell'];
		$user_province = $data['user_province'];
		$user_city = $data['user_city'];
		$user_sign = $data['user_sign'];
		$user_prize = $data['user_prize'];
		$user_tag = $data['user_tag'];
		$map = array();
		$user = D('User'); 
		$res = $user -> field('user_id')->find($user_id);
		if($res == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		if($user_nickname){
			$map['user_nickname'] = $user_nickname;
		}
		if($user_sex){
			if($user_sex == 1){
				$user_sex1 = '男';
			}elseif($user_sex == 2){
				$user_sex1 = '女';
			}
			$map['user_sex'] = $user_sex1;
		}
		if($user_constell){
			$user_constell2 = $this -> get_constell($user_constell);
			$map['user_constell'] = $user_constell2;
		}
		if($user_province){
			$map['user_province'] = $user_province;
		}
		if($user_city){
			$map['user_city'] = $user_city;
		}
		if($user_sign){
			$map['user_sign'] = $user_sign;
		}
		if($user_tag){
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
 				}
			}
			$user_tag1 = implode(',', $user_tag);
			$map['user_tag'] = $user_tag1;
		}
		if($user_prize){
			$map['user_prize'] = $user_prize;
		}
		$res = $user -> where("user_id = $user_id") ->save($map);
		if($res || $res==0){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
	}
	public function get_constell($user_constell){
			switch ($user_constell) {
				case 1:
					$user_constell2 = '水瓶座';
					break;
				case 2:
					$user_constell2 = '双鱼座';
					break;
				case 3:
					$user_constell2 = '白羊座';
					break;
				case 4:
					$user_constell2 = '金牛座';
					break;
				case 5:
					$user_constell2 = '双子座';
					break;
				case 6:
					$user_constell2 = '巨蟹座';
					break;
				case 7:
					$user_constell2 = '狮子座';
					break;
				case 8:
					$user_constell2 = '处女座';
					break;
				case 9:
					$user_constell2 = '天枰座';
					break;
				case 10:
					$user_constell2 = '天蝎座';
					break;
				case 11:
					$user_constell2 = '射手座';
					break;
				default:
					$user_constell2 = '摩羯座';
					break;	
			}
			return $user_constell2;
	}
}