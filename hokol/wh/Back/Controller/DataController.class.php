<?php
namespace Back\Controller;
use 	  Think\Controller;
class DataController extends Controller{
	public function a(){
		$a = $_SERVER['SERVER_NAME'];
		$b = $_SERVER ["SCRIPT_NAME"];
		dump($a);
		dump($_SERVER ["SCRIPT_NAME"]);
	}
	public function test(){
		$employer_comm = new \Api\Model\TaskemployerCommentModel();
		$res1 = $employer_comm -> $a;
		//dump($res1);
		$a = time();
		$b = date('Y-m-d H:i:s');
		$d = date('Y-m-d H:i:s',1488888888);
		$c = date('Y-m-d H:i:s',strtotime("$d +3 month"));
		$e = strtotime($c);
		//$f = mktime(0,0,0,2,36,2017);
		$g = $a - $c;
		dump($a);
		dump($b);
		dump($c);
		dump($d);
		dump($e);
	}
	public function test1(){
		//exit;
		$region = D("Region");
		if(!empty($a)){
			echo '111';
		}
		$a =file_get_contents("http://120.92.35.211/wanghong/wh/index.php/Api/ApiArea/get_area");
		dump($a);
	}
	public function test2(){
		$data = I("get.");
		$code = $data['code'];
		dump($code);
		session_id($code);
		session_start();
		if(session('a')){
			echo '登录成功';
			dump(session());
		}
		//dump(session_save_path());i4safq71l48h180nlt9frt5es2
		//http://120.92.35.211/wanghong/wh/Common/Uploads/user_logo/2017-03-23/58d3a8f869c5b.jpg
	}
	static function tree($arr,$parent_id){
		static $res = array();
		foreach ($arr as $v) {
			if($v['parent_id'] == $parent_id){
				foreach ($arr as $v1) {
					if($v1['parent_id'] == $v['region_id']){
						$v[$v['region_name']][] = $v1['region_name'];
						self::tree($arr,$v1['region_id']);
						$res[$v['region_name']] = $v[$v['region_name']];
					}

				}
			}
		}
		return $res;
	}
	public function find_employee(){
		$task_trace = D("User_task_trace");
		$task = D("User_task");
		$info = $task_trace ->field('id,task_id,user_id,is_confirm,is_commennt,employ_time,task_end_time')-> 
		where(array('is_employe'=>1,'is_commennt'=>0))->select();
		foreach ($info as $key => $value) {
			$id = $value['id'];
			$task_id = $value['task_id'];
			$time1 = $value['employ_time'];
			$time2 = $value['task_end_time'];
			$d1 = time()-$time1-7200;
			if($d1>10){	//雇员规定时间不接单
				$res1 = $task_trace -> where("id=$id")->setField('is_refuse',1);
				//向雇主发送消息
			}
			if($d2>10){//雇员规定时间不确认订单
				$r = $task -> field('task_unfinish_peo') -> find($task_id);//查询该任务的未完成人员
				$task_unfinish_peo = explode(',', $r['task_unfinish_peo']);
				if(in_array($user_id, $task_unfinish_peo)){//如果该用户在未完成人员中
					$res2 = $task_trace -> where("id=$id")->setField('is_finish',3);
				}else{
					$res2 = $task_trace -> where("id=$id")->setField('is_finish',1);
				}	
			}
			if($d3>10){//雇员规定时间不评价
				$res3 = $task_trace -> where("id=$id")->setField('is_commennt',1);
				//$res4 = $ -> //更新雇员评价表
			}
		}
	}
	public function find_employer(){
		$task = D("User_task");
		$info = $task -> field('is_confirm,is_commennt,is_employe,is_finish,task_comment_peo,task_confirm_peo,
			task_unfinish_peo,task_end_time')->where(array('is_commennt'=>0,'is_complain'=>0))->select();
		foreach ($info as $key => $value) {
			$time1 = time()-$info['task_end_time'];
			
		}
	}
	//获取用户的多条私密动态
 	public function dt_private_nums(){
 		//$json = file_get_contents('php://input');
		// $data = json_decode($json,true);
 		$data = I('get.');
 		$num1 = $data['num1'];
 		$d1 = $data['length'];
 		$user_id = $data['user_id'];
 		$user_id_find = $data['user_id_find'];//被查询的用户
 		$dt   = D('Dongtai');
 		$user = D('User');
 		$user_memb = D("User_member");
 		if($user_id !== $user_id_find){//用户查看的不是自己的私密空间
 			$res = $user_memb -> field('member_type,last_recharge_time')->find($user_id);
 			if($res === null || $res['member_type']==2){//如果没有数据，则该用户不是会员或者是会员类型2，无法查看
 				$output = array('code'=>3,'data'=>array());
				exit(json_encode($output));
 			}else{
 				$member_type = $res['member_type'];
 				$last_time = $res['last_recharge_time'];
 				$result = $this -> get_member($member_type,$last_time);
 			}
 		}else{
 			$result = 1;
 		}
 		$res1 = $dt -> field('dt_id')->where(array('dt_user_id'=>$user_id,'is_private'=>'1'))->select();//查出该用户的动态总数
 		//dump($res1);
 		$num = count($res1);
 		if($num < $num1){
 			$output = array('code'=>1,'data'=>array());
			exit(json_encode($output));
 		}else{
 			$res1 = $dt->field('wh_dongtai.dt_id,dt_small_img,dt_mid_img,dt_total_zan,dt_zan_people,
 				wh_user.user_id,user_nickname,user_logo,user_coin,user_city,dt_content,dt_pub_time')
 			->join('wh_dt_pics as dp on dp.dt_id=wh_dongtai.dt_id')
 			->join('wh_user on wh_user.user_id=wh_dongtai.dt_user_id')
 			->where(array('dt_user_id'=>$user_id,'is_private'=>'1'))->limit($num1,$d1)->select();//数据库查询数据
 			//dump($res);
 			if($res1 && $result){
				foreach ($res1 as $key => $value) {
				$zan = explode(',',$value['dt_zan_people']);
				//对标签以及点赞的人进行处理
				$map = array();
				$map['user_id'] = array('in',$zan);//构造in查询语句
				$user_nickname = $user -> field('user_nickname')->where($map)->select();
				$user_nicknames=array_column($user_nickname,'user_nickname');//二维数组转为一维数组
				$res1[$key]['dt_zan_people_nickname'] = $user_nicknames;
				unset($res1[$key]['dt_zan_people']);//删除数组键
				}
 				$output = array('code'=>0,'data'=>array('list'=>$res1));
 				exit(json_encode($output));
 			}else{
 				$output = array('code'=>2,'data'=>array());
 				exit(json_encode($output));
 			}
 		}
 	}
 	//获取用户的会员情况
 	public function get_member($type,$time){
 		if($type == 1){
 			$d = time()-($time+86400);
 			if($d<0){
 				$result = 1;
 			}else{
 				$result = 0;
 			}
 		}elseif ($type == 3) {
 			$last_time = date('Y-m-d H:i:s',$last_time);
			$expire_time = date('Y-m-d H-i-s',strtotime("$d +1 month"));
			$expire_time = strtotime($expire_time);
			$d = $time-$expire_time;
			if($d<0){
 				$result = 1;
 			}else{
 				$result = 0;
 			}
 		}elseif ($type == 4) {
 			$last_time = date('Y-m-d H:i:s',$last_time);
			$expire_time = date('Y-m-d H-i-s',strtotime("$d +3 month"));
			$expire_time = strtotime($expire_time);
			$d = $time-$expire_time;
			if($d<0){
 				$result = 1;
 			}else{
 				$result = 0;
 			}
 		}elseif ($type == 5) {
 			$last_time = date('Y-m-d H:i:s',$last_time);
			$expire_time = date('Y-m-d H-i-s',strtotime("$d +1 year"));
			$expire_time = strtotime($expire_time);
			$d = $time-$expire_time;
			if($d<0){
 				$result = 1;
 			}else{
 				$result = 0;
 			}
 		}
 		return $result;
 	}
 	//public function find_unconfirm(){
 	// 	create PROCEDURE pro_unconfirm_user(in n_time int)
 	// 	BEGIN
 	// 		DECLARE stop,n_user_id,n_task_id,n_employe_time int default 0;
 	// 		DECLARE cur cursor for select user_id,task_id,employe_time from wh_task_trace where is_confirm =0;
 	// 		DECLARE continue handler for not found SET stop=1;
 	// 		open cur;
 	// 		repeat
 	// 		fetch cur into n_user_id,n_task_id,n_employe_time;
 	// 		if not stop then 
 	// 			set d=n_time-employe_time;
 	// 			if time-n_employe_time>7200 then
 	// 				insert into wh_record(user_id,task_id) values (user_id,task_id);
 	// 			end if;
 	// 		end if;
 	// 		until stop end repeat;
 	// 		close cur;
 	// 	END
 	// }
 		public function login1(){	
		if(IS_POST){
			$output = array();
			$data = array();
			$data['token'] = $_POST['token'];
			$data['user_pwd'] = $_POST['user_pwd'];
			$data['user_tel']= $_POST['user_tel'];
			//dump($data['token']);

		//获取客户端提交的登录信息
		// $json = file_get_contents('php//input');
		// $data = json_decode($json,true);
			$user = D('User');
		//如果客户端提交token，则先认证token
			if($data['token']){
			//dump(S('token'));
				if(!S('token')){
					dump(S('token'));
					$output = array('code'=>0,'token'=>expire);
				exit(json_encode($output));//token失效，重新登录
			}
			if($data['token']==S('token')){
				$output = array('code'=>1,'token'=>success);
					exit(json_encode($output));//token正确，可以登录
				}else{
					$output = array('code'=>0,'token'=>fail);
					exit(json_encode($output));//token错误
				}
			}
		//客户端未提交token，则密码账户登录,并返回token
			else{
			//数据库验证登录信息
				$user_pwd = $data['user_pwd'];
				$user_tel = $data['user_tel'];
				dump($data);
				$res = $user -> where(array('user_tel'=>$user_tel,'user_pwd'=>$user_pwd))
				->find();
			if($res){//验证成功
				$token = md5(uniqid());//生成token
				S('token',$token);//将token缓存，并缓存设置时间
				$output = array('code'=>1,'token'=>$token);
				exit(json_encode($output));
			}
			else{//验证失败
				$output = array('code'=>0,'token'=>null);
				exit(json_encode($output));
			}
		}
		}else{
			$this->display();
		}		
	}
 }
