<?php
namespace Api\Controller;
use Think\Controller;
class ApiMineController extends Controller{	
	//获取用户粉丝信息（多条）
	public function user_fans_info(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		// $data = I('get.');
		$num1 = $data['num1'];
		$d1 = $data['length'];
		$user_id = $data['user_id'];
		$user = D('User');
		$map = array();
		$res = $user -> where(array('user_id'=>$user_id))->
		field('user_fans,user_care')->find();//查出用户粉丝数据及关注的用户
		if($res == null){//返回数据为null，该用户不存在
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}elseif($res['user_fans'] == null){//返回user_fans为null
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
		$fans1 =$res['user_fans'];
		$user_care = $res['user_care'];
		$fans = explode(',',$fans1);//将字符串数据改为数组
		$user_cares = explode(',', $user_care);
		$map['user_id'] = array('in',$fans);
		$res = $user -> field('user_id,user_logo,user_sign,user_sex,user_nickname,user_tag,user_zan')->
		where($map)->limit($num1,$d1)->select();
		if($res){
			foreach($res as $key => $value){
				$tag = explode(',',$value['user_tag']);
				$res[$key]['user_tag'] =$tag;
				$k = in_array($value['user_id'],$user_cares);
					//dump($k);
				if($k == false){
					$res[$key]['is_care'] = 0;
				}else{
					$res[$key]['is_care'] = 1;
				}
			}
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}else{
			$output = array('code'=>2000,'data'=>array());
			exit(json_encode($output));
		}
	}
	//获取用户收藏的任务(多条)
	public function user_collect_task(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$num1 = $data['num1'];
		$d1 = $data['length'];
		$user = D('User');
		$task = D('User_task');
		$res = $user -> field('user_collect_task')->where("user_id=$user_id")->find();//查询用户收藏的任务
		if($res == null){//返回数据为null，该用户不存在
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}elseif($res['user_collect_task'] == null){//返回user_fans为null
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
		$user_collect_task1 = $res['user_collect_task'];//从数组中取出收藏的任务
		$user_collect_task = explode(',', $user_collect_task1);
		$map = array();
		$map['task_id'] = array('in',$user_collect_task);//构造in条件，task_id in $user_collect_task
		$res = $task ->field('task_id,task_fee,task_title,user_nickname,user_logo,task_end_time')->
		join('wh_user on wh_user_task.task_id=wh_user.user_id')->where($map)->limit($num1,$d1)->select();
			//dump($res);
		foreach ($res as $key => $value) {
				$task_rem_time = $res[$key]['task_end_time'] -time();//计算出任务剩余时间				
				$res[$key]['task_rem_time'] = $task_rem_time;
			}
			//dump($res);
			if($res){
				$output = array('code'=>0,'data'=>array('list'=>$res));
				exit(json_encode($output));
			}else{
				$output = array('code'=>2000,'data'=>array());
				exit(json_encode($output));
			}
	}
	//用户关注，取消关注好友
	public function user_care_switch(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$user_id_other = $data['user_id_other'];
		$care = $data['care'];
		$user = D('User');
		$map['user_id'] = array('in',array($user_id,$user_id_other));
 		$res = $user->field('user_id')->where($map)->select();//数据库查询用户是否存在
 		//dump($res);
 		if(count($res)!==2){//返回res不是两条数据】
 			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
 		}
		$res = $user -> field('user_care') ->find($user_id);//取出用户关注的人
		$user_care = explode(',',$res['user_care']);//从数组中取出数据
		//dump($user_care);
		if($care == 1){//实现加关注功能
			if(in_array($user_id_other,$user_care)){
				$output = array('code'=>3000,'data'=>array());
				exit(json_encode($output));
			}else{
				if($res['user_care'] == null){//如果用户原关注量为0，直接赋值
					$user_care1 = $user_id_other;
				}else{//如果用户原关注数不为0，则进行数据拼接
					$user_care1 = $res['user_care'].','.$user_id_other;
				}
				$data['user_care'] = $user_care1;
				$data['user_care_num'] = array('exp','user_care_num+1');
				$res1 = $user ->where("user_id = $user_id")->save($data);
				$output = array('code'=>0,'data'=>(object)array());
				exit(json_encode($output));
			}
		}else{
			$key = array_search($user_id_other, $user_care);//判断用户是否关注
			//dump($key);
			if($key === false){//如果用户没有关注，则返回code3
				$output = array('code'=>3000,'data'=>array());
				exit(json_encode($output));
			}elseif($key !== false){
				unset($user_care[$key]);//删除对应数据，实现删除功能
				$user_care1 = implode(',', $user_care);//将数组转为字符串形式
				$data['user_care'] = $user_care1;//构造新的关注数据
				$data['user_care_num'] = array('exp','user_care_num-1');//将关注人数减一
				$res1 = $user ->where("user_id = $user_id")->save($data);//更新到数据库
				$output = array('code'=>0,'data'=>(object)array());
				exit(json_encode($output));
			}
		}	
	}
	//用户送红豆
	public function present_coin(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];//用户ID
		$recive_user_id = $data['recive_user_id'];//被赠与红豆用户ID
		$coin_num = $data['coin_num'];
		$dt_type = $data['dt_type'];//获取动态类型,0：不是通过动态送红豆,1:普通动态,2:私密空间
		$id =$data['id'];//获取动态/私密动态的id
		$user = D('User');
		$dt = D('Dongtai');
		$pri = D('Private_space');
		$user_recive = D('User_recive_coin');
		$map['user_id'] = array('in',array($user_id,$recive_user_id));
 		$res = $user->field('user_id')->where($map)->select();//数据库查询用户是否存在
 		if(count($res)!==2){//返回res不是两条数据】
 			$output = array('code'=>2001,'data'=>array());
 			exit(json_encode($output));
 		}
 		$coin = $user -> field('user_coin')->find($user_id);//统计用户的红豆数
 		$user_coin = $coin['user_coin'];//从数组中取出数据
 		$d = $user_coin-$coin_num;//比较任务所需红豆与总红豆
 		if($d < 0){//如果用户红豆数不足，则返回code：2
 			$output = array('code'=>3000,'data'=>array());
 			exit(json_encode($output));
 		}
		$user -> startTrans();//开启事务
		$arr['user_id'] = $recive_user_id;
		$arr['from_user_id'] = $user_id;
		$arr['add_time'] = time();
		$arr['coin_num'] = $coin_num;
		$user_recive ->create($arr);
		$res1 = $user_recive -> add();
		$res3 = $user -> where("user_id = $user_id")->setDec('user_coin',$coin_num);
		$res2 = $user -> where("user_id = $recive_user_id")->setInc('user_coin',$coin_num);
		if($dt_type==1){
			$res4 = $dt -> where("dt_id = $id") -> setInc('coin_num',$coin_num);
			if($res2 && $res1 && $res3 && $res4){
			$user ->commit();//数据操作都成功，提交事务
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
			}
		}elseif ($dt_type==2) {
			$res4 = $pri -> where("pri_id = $id") -> setInc('coin_num',$coin_num);
			if($res2 && $res1 && $res3 && $res4){
			$user ->commit();//数据操作都成功，提交事务
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
			}
		}elseif($res2 && $res1 && $res3){
			$user ->commit();//数据操作都成功，提交事务
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
	}
	//我的会员
	public function mine_member(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];
		$user_member = D("User_member");
		$user = D("User");
		$res = $user_member -> field('expire_time,member_type') ->where(array('user_id'=>$user_id))->find();
		if($res==null){//res为空
			$output = array('code'=>2006,'data'=>array());
			exit(json_encode($output));
		}elseif($res['expire_time']-time()<0){//会员已经过期
			$output = array('code'=>2007,'data'=>array());
			exit(json_encode($output));
		}elseif($res){
			$call_card = D("Call_card_record");
			$map['user_id'] = array('eq',$user_id);
			$map['expire_time'] = array('gt',time());
			$num = $call_card -> where($map)->sum('rem_num');
			$output = array('code'=>0,'data'=>array('call_card_num'=>$num,
				'expire_time'=>$res['expire_time'],'member_type'=>$res['member_type']));
			exit(json_encode($output));
		}
	}
	//购买一次打电话权限
	public function recharge_once_call(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
 		//$data = I('get.');
		$user_id = $data['user_id'];
		$user = D("User");
		$member_record = D("User_member_record");
		$call_card = D("Call_card_record");
		$res = $user -> field('user_coin')->where(array('user_id'=>$user_id))->find();
		if($res == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}elseif($res['user_coin']-200<0){
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}else{
			$add['user_id'] = $user_id;
			$add['add_time'] = time();
			$add['expire_time'] = time()+315360000;//默认有效时间为10年
			$add['call_card_num'] = 1;
			$add['rem_num'] = 1;
			$user -> startTrans();
			$call_card -> create($add);
			$res1 = $call_card-> add();
			$add['member_type'] = 4;
			$add['begin_time'] = 0;
			$add['expire_time'] = 0;
			$add['recharge_fee'] = 200;
			$member_record ->create($add); 
			$res2 = $member_record-> add();
			$res3 = $user -> where(array('user_id'=>$user_id))->setDec('user_coin',200);
			if($res && $res2 && $res3){
			    $user ->commit();
			    $output = array('code'=>0,'data'=>( object)array());
			    exit(json_encode($output));
			}
		}
	}
	//购买一次看空间权限
	public function recharge_once_space(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
 		//$data = I('get.');
		$user_id = $data['user_id'];
		$member_record = D("User_member_record");
		$user = D("User");
		$res = $user -> field('user_coin')->where(array('user_id'=>$user_id))->find();
		if($res == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}elseif($res['user_coin']-80<0){
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}else{
			$arr['user_coin'] = array('exp','user_coin-80');
			$arr['user_see_card'] = array('exp','user_see_card+1');
			$user -> startTrans();
			$res1 = $user->where(array('user_id'=>$user_id))->save($arr);
			$add['user_id'] = $user_id;
			$add['add_time'] = time();
			$add['member_type'] = 5;
			$add['expire_time'] = 0;
			$add['begin_time'] = 0;
			$add['recharge_fee'] = 80;
			$member_record -> create($add);
			$res2 = $member_record -> add();
            if($res1 && $res2){
                $user ->commit();
                $output = array('code'=>0,'data'=>(object)array());
                exit(json_encode($output));
            }	
		}
	}
	//购买会员
	public function recharge_vip(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$recharge_type = $data['recharge_type'];
		switch($recharge_type) {
			case 1;
				$coin_num=300;
			break;
			case 2:
				$coin_num=1000;
			break;
			case 3:
				$coin_num=4000;
			break;
		}
		$user = D('User');
		$member_record = D("User_member_recod");
		$user_member = D('User_member');
		$call_card = D("Call_card_record");
		$coin = $user -> field('user_coin') -> find($user_id);
		if($coin == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		$user_coin = $coin['user_coin'];
		$d = $user_coin-$coin_num;
		if($d < 0){
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}else{
			$res = $user_member -> field('id,expire_time,member_type,recharge_total,user_call_card') -> find($user_id);
			if($res){//续费会员
				$member_type = $res['member_type'];//获取会员类型
				$expire_time = $res['expire_time'];//取出过期时间，用于计算新的过期时间
				/*判断用户续费类型,判断是在会员期内续费还是会员期外续费*/
				if($expire_time < time()){//已经不在会员期
					$expire_time = time();//会员期外续费需将过期时间改为当前时间
				}
				$total = $res['recharge_total'] + $coin_num;//计算本次充值后的充值总金额 
				$add = $this->get_expiretime_card($recharge_type,$expire_time);
				if($member_type > $recharge_type){
					$type = $member_type;
				}else{
					$type = $recharge_type;
				}
				$add['member_type'] = $type;
				$add['recharge_total'] = $coin_num+$res['recharge_total'];
				$user_member -> startTrans();
				$res1 = $user_member -> where("user_id=$user_id")->save($add);
				$res2 = $user -> where("user_id =$user_id")->setDec('user_coin',$coin_num);
				$add['user_id'] = $user_id;
				$add['add_time'] = time();
				$add['begin_time'] = $expire_time;//续费会员的起效时间即为
				$add['recharge_fee'] = $coin_num;
				$member_record ->create($add);
				$res3 = $member_record ->add();
				$add['rem_num'] = $add['call_card_num'];
				$call_card ->create($add);
				$res4 = $call_card ->data($add)->add();
			}else{//第一次充值会员
				$time = time();
				$add = $this->get_expiretime_card($recharge_type,$time);
				$add['user_id'] = $user_id;
				$add['member_type'] = $recharge_type;
				$add['member_reg_time'] = time();
				$add['last_recharge_time'] = time();
				$add['recharge_total'] = $coin_num;
				$user_member -> startTrans();
				$res1 = $user_member -> create($add)->add();
				$res2 = $user -> where("user_id =$user_id")->setDec('user_coin',$coin_num);
				$add['add_time'] = time();//此次充值添加时间
				$add['begin_time'] = time();//会员起效时间(第一次充值与add_time相同)
				$add['recharge_fee'] = $coin_num;
				$member_record ->create($add);
				$res3 = $member_record ->data($add)->add();
				$add['rem_num'] = $add['call_card_num'];
				$call_card ->create($add);
				$res4 = $call_card ->data($add)->add();
			}
		}
		if($res1 && $res2 &&$res3 &&$res4){
			$user_member -> commit();
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
	} 
	//获取会员的过期时间、交流券
	/* $type为充值类型
	   $time为上次会员过期时间
	   $recharge_total为此次充值后的充值总额
	*/
	public function get_expiretime_card($type,$time){
		if(time()-$time>0){//续费会员时，如果当下时间已大于会员过期时间，那么会员时效要以当下时间为准
			$time = time();
		}
		if($type == 1){
			$d = date('Y:m:d H:i:s',$time);
			$c = date('Y:m:d H:i:s',strtotime("$d +1 month"));
			$expire_time = strtotime($c);
			$call_card_num = 15;
		}elseif($type == 2){
			$d = date('Y:m:d H:i:s',$time);
			$c = date('Y:m:d H:i:s',strtotime("$d +3 month"));
			$expire_time = strtotime($c);
			$call_card_num = 60;
		}elseif($type == 3){
			$d = date('Y:m:d H:i:s',$time);
			$c = date('Y:m:d H:i:s',strtotime("$d +1 year"));
			$expire_time = strtotime($c);
			$call_card_num = 280;
		}
		//$res['member_level'] = get_member_level($recharge_total);
		$res['expire_time'] = $expire_time;
		$res['call_card_num'] = $call_card_num;
		return $res;
	}
	//获取用户等级
	public function get_user_level($total){
		if($total==0){
			$level = 1;
		}elseif(0<$total&&$total <=200){
			$level = 2;
		}elseif (200<$total&&$total<=500) {
			$level = 3;
		}elseif (500<$total&&$total<=1000) {
			$level = 4;
		}elseif (1000<$total&&$total<=2000) {
			$level = 5;
		}elseif (2000<$total&&$total<=5000) {
			$level = 6;
		}elseif (5000<$total&&$total<=10000) {
			$level = 7;
		}elseif (10000<$total&&$total<=20000) {
			$level = 8;
		}elseif (20000<$total&&$total<=30000) {
			$level = 9;
		}elseif (30000<$total&&$total<=45000) {
			$level = 10;
		}elseif (45000<$total&&$total<=60000) {
			$level = 11;
		}elseif (60000<$total&&$total<=80000) {
			$level = 12;
		}elseif (80000<$total&&$total<=100000) {
			$level = 13;
		}elseif (100000<$total&&$total<=150000) {
			$level = 14;
		}elseif (150000<$total&&$total<=200000) {
			$level = 15;
		}elseif (200000<$total&&$total<=250000) {
			$level = 16;
		}elseif (250000<$total&&$total<=300000) {
			$level = 17;
		}elseif (300000<$total&&$total<=350000) {
			$level = 18;
		}elseif (350000<$total&&$total<=400000) {
			$level = 19;
		}elseif (400000<$total&&$total<=450000) {
			$level = 20;
		}elseif (450000<$total&&$total<=500000) {
			$level = 21;
		}elseif (500000<$total&&$total<=550000) {
			$level = 22;
		}elseif (550000<$total&&$total<=600000) {
			$level = 23;
		}elseif (600000<$total&&$total<=650000) {
			$level = 24;
		}elseif (650000<$total&&$total<=700000) {
			$level = 25;
		}elseif (700000<$total&&$total<=750000) {
			$level = 26;
		}elseif (750000<$total&&$total<=800000) {
			$level = 27;
		}elseif (800000<$total&&$total<=850000) {
			$level = 28;
		}elseif (900000<$total&&$total<=950000) {
			$level = 29;
		}elseif (950000<$total&&$total<=1000000) {
			$level = 30;
		}elseif (1000000<$total&&$total<=2000000) {
			$level = 31;
		}elseif (2000000<$total&&$total<=3000000) {
			$level = 32;
		}elseif (3000000<$total&&$total<=4000000) {
			$level = 33;
		}elseif (4000000<$total&&$total<=5000000) {
			$level = 34;
		}elseif (5000000<$total&&$total<=6000000) {
			$level = 35;
		}elseif (6000000<$total&&$total<=7000000) {
			$level = 36;
		}elseif (7000000<$total&&$total<=8000000) {
			$level = 37;
		}elseif (8000000<$total&&$total<=9000000) {
			$level = 38;
		}elseif (9000000<$total&&$total<=10000000) {
			$level = 39;
		}elseif (10000000<$total&&$total<=20000000) {
			$level = 40;
		}
		return $level;
	}
	//我的消息
	public function mine_message(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		// $num1 = $data['num1'];
		// $d1 = $data['length'];
		$user_mess = D("User_message");
		$user = D("User");
		$user_id = $user_mess->field('user_id')->find($user_id);//查询该用户
		if($user_id == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		$res = $user_mess -> where("user_id = $user_id") ->order('pub_time desc')->select();
		if($res == null){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}else{
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}
	}
	//我的消息删除
	public function message_delete(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];
		$mess_ids = $data['mess_ids'];//获取用户勾选的消息[数组形式]
		$user_mess = D("User_message");
		$user_id = $user_mess->field('user_id')->find($user_id);//查询该用户
		if($user_id == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		$map['mess_id'] = array('in',"$mess_ids");//构造in查询语句
		$user = $user_mess -> field('user_id') -> where($map)->select();//获取消息的user，并与接受的user_id对比
		$user1 = array_unique($user);//把数组中数据相同的数据合一
		$num = count($user1);//统计用户数量，为1表示用户只有一个，符合逻辑
		$user_id1 = $user1['0']['user_id'];//得出数组第一个数据并且等于user_id符合逻辑
		if($num==1 && $user_id1==$user_id){//只有上面两个条件都满足，才进行下面逻辑
			$map['user_id'] = $user_id;
			$res = $user_mess -> where($map) -> delete();
			if($res){
				$output = array('code'=>0,'data'=>(object)array());
				exit(json_encode($output));
			}
		}else{
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}	
	}
	//我的消息读取
	public function message_read(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];
		$mess_ids = $data['mess_ids'];//获取用户勾选的消息[数组形式]
		$user_mess = D("User_message");
		$user = D("User");
		$user_id = $user_mess->field('user_id')->find($user_id);//查询该用户
		if($user_id == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		$map['mess_id'] = array('in',"$mess_ids");//构造in查询语句
		$map['is_read'] = 0;
		$user = $user_mess -> field('user_id') -> where($map)->select();//获取消息的user，并与接受的user_id对比
		$user1 = array_unique($user);//把数组中数据相同的数据合一
		$num = count($user1);//统计用户数量，为1符合逻辑
		$user_id1 = $user1['0']['user_id'];//得出数组第一个数据并且等于user_id符合逻辑
		if($num==1 && $user_id1==$user_id){//只有上面两个条件都满足，才进行下面逻辑
			$map['user_id'] = $user_id;
			$res = $user_mess -> where($map) -> setField('is_read',1);
			if($res){
				$output = array('code'=>0,'data'=>(object)array());
				exit(json_encode($output));
			}
		}else{
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}	
	}
	//用户送出的红豆
	public function post_gift(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$num1 = $data['num1'];
		$d1 = $data['length'];
		$user_id = $data['user_id'];
		$user_rec = D("User_recive_coin");
		$user = D("User");
		$res = $user -> field('user_id')->find($user_id);
		if($res==null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		$res = $user_rec ->field('user_id,coin_num,add_time')-> where("from_user_id=$user_id")
		->order('add_time desc')->limit($num1,$d1)->select();
		if($num1==0&&$res==0){
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}elseif($res){
			foreach ($res as $key => $value) {
				$user_id = $value['user_id'];
				$res1 = $user -> field('user_logo,user_nickname')->find($user_id);
				$res[$key]['user_logo'] = $res1['user_logo'];
				$res[$key]['user_nickname'] = $res1['user_nickname'];
			}
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}else{
			$output = array('code'=>2000,'data'=>array());
			exit(json_encode($output));
		}
	}
	//用户收到的红豆
	public function recive_gift(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$num1 = $data['num1'];
		$d1 = $data['length'];
		$user_id = $data['user_id'];
		$user_rec = D("User_recive_coin");
		$user = D("User");
		$res = $user -> field('user_id')->find($user_id);
		if($res==null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		$res = $user_rec ->field('from_user_id,coin_num,add_time')-> where("user_id=$user_id")
		->order('add_time desc')->limit($num1,$d1)->select();
		foreach ($res as $key => $value) {
			$user_id = $value['from_user_id'];
			$res1 = $user -> field('user_logo,user_nickname')->find($user_id);
			$res[$key]['user_id'] = $value['from_user_id'];//将键值from_user_id改为user_id
			$res[$key]['user_logo'] = $res1['user_logo'];
			$res[$key]['user_nickname'] = $res1['user_nickname'];
			unset($res[$key]['from_user_id']);
		}
		//dump($res);
		if($num1==0&&$res==0){
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}elseif($res){
			foreach ($res as $key => $value) {
				$user_id = $value['from_user_id'];
				$res1 = $user -> field('user_logo,user_nickname')->find($user_id);
				$res[$key]['user_logo'] = $res1['user_logo'];
				$res[$key]['user_nickname'] = $res1['user_nickname'];
			}
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}else{
			$output = array('code'=>2000,'data'=>array());
			exit(json_encode($output));
		}
	}
	//会员充值记录
	public function recharge_vip_record(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];
		$member_record = D("User_member_recod");
		$user = D("User");
		$res1 = $user -> field('user_id')->where(array('user_id'=>$user_id))->find();
		if($res1==null){
			$output = array('code'=>2001,'data'=>(object)array());
			exit(json_encode($output));
		}
		$res = $member_record ->field('user_id,add_time,member_type,expire_time,begin_time,recharge_fee')
		-> where(array('user_id'=>$user_id))->select();
		if(count($res)==0){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}else{
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}	
	}
	//未使用的交流券
	public function rem_call_card(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$call_card = D("Call_card_record");
		$user = D("User");
		$res1 = $user -> field('user_id')->where(array('user_id'=>$user_id))->find();
		if($res1==null){
			$output = array('code'=>2001,'data'=>(object)array());
			exit(json_encode($output));
		}
		$map['expire_time'] = array('gt',time());//未使用的交流券，过期时间一定是大于当前时间的
		$map['user_id'] = $user_id;
		$res = $call_card ->field('rem_num,expire_time')-> where($map) -> select();
		if(count($res)==0){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}else{
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}
	}
	//已使用的交流券(显示最近一个月使用的)
	public function used_call_card(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$call_card = D("Call_card_record");
		$user = D("User");
		$res1 = $user -> field('user_id')->where(array('user_id'=>$user_id))->find();
		if($res1==null){
			$output = array('code'=>2001,'data'=>(object)array());
			exit(json_encode($output));
		}
		$map['expire_time'] = array('gt',time()-2592000);//已使用的券，expire_time大于过去一个月
		$map['user_id'] = $user_id;
		$res = $call_card ->field('used_num,expire_time')-> where($map) -> select();
		dump($res);
		if(count($res)==0){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}else{
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}
	}
	//已过期的交流券(显示最近一个月使用的)
	public function expire_call_card(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$call_card = D("Call_card_record");
		$user = D("User");
		$res1 = $user -> field('user_id')->where(array('user_id'=>$user_id))->find();
		if($res1==null){
			$output = array('code'=>2001,'data'=>(object)array());
			exit(json_encode($output));
		}
		$t1 = time()-2592000;
		$t2 = time(); 
		$map['expire_time'] = array('between',"$t1,$t2");//expire_time为过去一个月到现在的
		$map['user_id'] = $user_id;
		$res = $call_card ->field('rem_num,expire_time')-> where($map) -> select();
		dump($res);
		if(count($res)==0){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}else{
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}
	}
	//充值记录
	public function recharge_record(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];
		$recharge_record = D("Recharge_record");
		$user = D("User");
		$res = $user -> field('user_id')->find($user_id);
		if($res==null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		$res = $recharge_record ->field('add_time,recharge_num')-> where("user_id=$user_id")->order('add_time desc')->select();
		//dump($res);
		$output = array('code'=>0,'data'=>array('list'=>$res));
		exit(json_encode($output));
	}
	//用户联系
	public function call(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		$user_id = $data['user_id'];//获取用户ID
		$user_id_call = $data['user_id_call'];//获取被呼叫的用户ID
		$call_card = D("Call_card_record");
		$user = D("User");
		$virtual_tel = D("Virtual_tel");
		$map['user_id'] = $user_id;
		$map['expire_time'] = array('gt',time());
		$card_num = $call_card -> where($map) -> sum('call_card_num');//数据库取出该用户的交流券数
		//dump($card_num);
		if($card_num < 1){//如果该用户没有交流券
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}else{
			$res = $user ->field('user_tel') -> where(array('user_id'=>$user_id_call)) -> find();//数据库取出被叫用户的号码
			if($res == null){
				$output = array('code'=>2001,'data'=>array());
				exit(json_encode($output));
			}else{
				$user_tel = $res['user_tel'];
				$a = $virtual ->field('virtual_num')-> where(array('callee'=>$user_tel))->find();//数据库查询该用户是否绑定虚拟号 
				if($a == null){//如果该号码未进行绑定，那就绑定该号码
					$res1 = $this -> get_virtual_num();//获取未绑定虚拟号以及cityId
					$result = $this -> connect_tel($user_tel,$res1['virtual_num'],$res1['cityId']);
					if($result['bindId'] !== null){
						$arr['bind_id'] = $result['bindId'];
						$arr['callee'] = $user_tel;
						$arr['caller_id'] = $user_id;
						$arr['virtual_num'] = $res1['virtual_num'];
						$res = $virtual_tel -> data($arr) -> add();
						$output = array('code'=>0,'data'=>array('virtual_num'=>$res1['virtual_num']));
						exit(json_encode($output));
					}else{
						$output = array('code'=>2000,'data'=>array());
						exit(json_encode($output));
					}
				}else{
					$output = array('code'=>0,'data'=>array('virtual_num'=>$a['virtual_num']));
					exit(json_encode($output));	
				}
			}
		}
	}
	//获取未绑定的中间号码以及cityId
	public function get_virtual_num(){
		$virtual_tel = D("Virtual_tel");
		$virtual_nums = $virtual_tel -> field('virtual_num') -> select();//获取已绑定的虚拟号
		$virtual_nums = array_column($virtual_nums,'virtual_num');
		$all_nums = array('1','2','3','40');//平台所拥有的虚拟号
		$city_id = array('170'=>'0023','1230'=>'3021','180'=>'23265');//虚拟号码对应的cityId
		//dump($virtual_nums);
		if(empty($virtual_nums)){
			$key = array_rand($all_nums,1);//返回该数组随机数据的键值
			$res['virtual_num'] = $all_nums[$key];//取出随机数据
			$a = $all_nums[$key];
			$res['cityId'] = $city_id[$a];//取出该中间号码的cityId
			return $res;
		}elseif(!empty($virtual_nums)){
			foreach ($all_nums as $key => $value) {
				if(!in_array($value, $virtual_nums)){
				//dump($value);
				$res['virtual_num'] = $value;
				$res['cityId'] = $city_id[$value];
				return $res;
				}
			}
		}else{
			$output = array('code'=>2000,'data'=>array());
			exit(json_encode($output));
		}	
	}
    //请求电话
    public function send_post(){
        $time = date('YmdHis');
        $account_sid = 'e270e220daacd078192ca279948232da';
        $account_token = '029c66f311a1e38d39a75118adb6ab8c';
        //$appId = '548b777057714ad693faf8bc5f0f970a';
        $data = $account_sid.':'.$time;
        dump($data);
        $auth = base64_encode($data);
        dump($auth);
        exit;
        $sig = md5($account_id.$account_token.$time);
        $sig = strtoupper($sig);
        $auth = 'ZTI3MGUyMjBkYWFjZDA3ODE5MmNhMjc5OTQ4MjMyZGE6MjAxNzA1MjIxNjUwMzI=';
        $sig = '98639F1CC6A915F7645433558E8C5496';
        $url = "https://api.ucpaas.com/2014-06-30/Accounts/e270e220daacd078192ca279948232da/safetyCalls/chooseNumber?sig=98639F1CC6A915F7645433558E8C5496";
       // dump($url);
        $post_data = array(
            'appId' => "548b777057714ad693faf8bc5f0f970a",
            'caller' => "008613053285282",
            'dstVirtualNum' => "17098913607",
            'virtualType' => "1",
            'name' => "王强",
            'cardtype' => 0,
            'cardno' => "341222199511249174",
            'cityId' => "008620"  
        );
        //创建连接
        $postdata = http_build_query($post_data);
        dump($postdata);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Host: 127.0.0.1\r\n"."Accept:application/json\r\n"."accept-encoding:gzip,deflate\r\n".
                "connection:Keep-Alive\r\n"."content-length:316\r\n"."content-type:application/json;charset:utf-8\r\n".
                "Authorization:$auth\r\n\r\n",
                'content' => $postdata,
                'timeout' => 30 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        dump($context);
        die;
        $result = file_get_contents($url,false,$context);
        dump($result);
    }
    //获取虚拟号码http请求返回结果
    public function get_status($url,$data_string,$auth){
    	$header = array(  
        	"Host: 127.0.0.1",
            "Accept: application/json",
            "Accept-encoding: gzip,deflate", 
            // "Connection: Keep-Alive",
            // "Content-length: ".$len,
            "Content-Type: application/json; charset=utf-8",
           	"Authorization: ".$auth
           	);
    	//dump($header);
        $ch = curl_init($url);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);  
        curl_setopt($ch, CURLOPT_POST, 1);  
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);	//检查证书中是否设置域名,0不验证;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	//信任任何证书;
        //curl_setopt($ch, CURLOPT_URL, $url);  
        $output = curl_exec($ch);  
       	echo $output;
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($status != 200){
			$output = (object)array(
				'status' => $status,
				'errno' => curl_errno($ch),
				'error' => curl_error($ch)
			);
		}else{
			$output = json_decode($output,true);
		} 
		curl_close($ch);
		return $output;
    }  
    //连接中间号与用户号码
    public function connect_tel(){
    	$time = date('YmdHis');
        $account_token = '029c66f311a1e38d39a75118adb6ab8c';
        $account_sid = 'e270e220daacd078192ca279948232da';
        $data = $account_sid.':'.$time;
        $auth = base64_encode($data);  
        $sig = md5($account_sid.$account_token.$time);
        $sig = strtoupper($sig);
    	$post_data = array(
            'appId' => "548b777057714ad693faf8bc5f0f970a",
            'callee' => "008613053285282",
            'dstVirtualNum' => "17092804143",
            'virtualType' => "1",
            'name' => "王强",
            'cardtype' => '0',
            'cardno' => "341222199511249174",
            'cityId' => "008628",
            'statusUrl' => "http://120.92.35.211/wanghong/wh/index.php/Api/ApiMine/get_info",
            'maxAge' => "40"
        );
		$data_string = json_encode($post_data);
		$url = "https://api.ucpaas.com/2014-06-30/Accounts/e270e220daacd078192ca279948232da/safetyCalls/chooseNumber?sig=".$sig;  
      	$a = $this -> get_status($url,$data_string,$auth);
      	dump($a);
      	return $a;
    }
    //解绑中间号与用户号码
    public function get2(){
    	$time = date('YmdHis');
        $account_token = '029c66f311a1e38d39a75118adb6ab8c';
        $account_sid = 'e270e220daacd078192ca279948232da';
        $data = $account_sid.':'.$time;
        $auth = base64_encode($data);  
        $sig = md5($account_sid.$account_token.$time);
        $sig = strtoupper($sig);
    	$post_data = array(
            'appId' => "548b777057714ad693faf8bc5f0f970a",
            'bindId' => '429fc2ee93554cd297abaa6c3b2ac564',
            'cityId' => "008628",
        );
		$data_string = json_encode($post_data);
		//dump($data_string);
		$url = "https://api.ucpaas.com/2014-06-30/Accounts/e270e220daacd078192ca279948232da/safetyCalls/unbindNumber?sig=".$sig;  
      	$a = $this -> get_status($url,$data_string,$auth);
      	dump($a);
    }
    //接收通话后的状态
    public function get_info(){
    	$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		$call_card = D("Call_card_record");
		$virtual_tel = D("Virtual_tel");
		if($data['callStatus'] == 'disconnected'){//正常通话后进行后续逻辑处理
			$user_id = $virtual_tel -> field('caller_id') -> where(array('bind_id'=>$data['bindId']))->find();
			/*将主叫用户的交流券减一*/
			$map['user_id'] = $user_id['caller_id'];
			$map['expire_time'] = array('gt',time());
			$res = $call_card -> where($map) -> order('expire_time',desc) -> limit(1) ->setDec('rem_num',1);//将该用户的交流券减一
		}
		$output = array('rtcode'=>0);
		exit(json_encode($output));
    }
    //用户信用
    public function user_credit(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$user  = D("User");
		$taskemployer_comm = D("Taskemployer_comment");
		$taskemployee_comm = D("Taskemployee_comment");
		/*获取用户资料完整度评分*/
		$res = $user -> field('user_nickname,user_logo,user_sex,user_constell,user_tag,
			user_province,user_city,user_sign')->where(array('user_id'=>$user_id))->find();
		//dump($res);
		$a = 0;
		foreach ($res as $key => $value) {
			if(!empty($value)){
				$a = $a+1;
			}
		}
		$score1 = ($a/8)*100;
		//dump($score);
		/*获取用户作为雇主的评分*/
		$res1 = $taskemployee_comm ->field('conformity_score,communion_score,credibility_score')
		->where(array('task_user_id'=>$user_id))->select();
		//dump($res1);
		$num = count($res1);
		if($num == 0){
			$score2['conformity_score'] = 5;
			$score2['communion_score'] = 5;
			$score2['credibility_score'] = 5;
		}else{
			$score2 = $this -> get_score('conformity_score','communion_score','credibility_score',$res1,$num);
		}
		/*获取用户作为雇员的评分*/
		$res2 = $taskemployer_comm ->field('conformity_score,action_capacity_score,attitude_score')
		->where(array('comment_user_id'=>$user_id))->select();
		//dump($res2);
		$num = count($res2);
		if($num == 0){
			$score3['conformity_score'] = 5;
			$score3['action_capacity_score'] = 5;
			$score3['attitude_score'] = 5;
		}else{
			$score3 = $this -> get_score('conformity_score','action_capacity_score','attitude_score',$res2,$num);
		}
		$output = array('code'=>0,'data'=>array('score1'=>$score1,'score2'=>$score2,'score3'=>$score3));
		exit(json_encode($output));
    }
    //获取用户的信用评分
    public function get_score($a,$b,$c,$res,$num){
		$score[$a] = 0;
		$score[$b] = 0;
		$score[$c] = 0;
		foreach ($res as $key => $value) {
			$score[$a] = $score[$a] + $value[$a];
			$score[$b] = $score[$b] + $value[$b];
			$score[$c] = $score[$c] + $value[$c];
		}
		$score[$a] = round($score[$a]/$num,1);
		$score[$b] = round($score[$b]/$num,1);
		$score[$c] = round($score[$c]/$num,1);
		return $score;
    }
    //我的评分(已发任务)
    public function mine_score_pub(){
    	$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$taskemployee_comm = D("Taskemployee_comment");
		$task = D("User_task");
		$user = D("User");
		/*取出用户发出任务(已评价的)*/
		$res1 = $taskemployee_comm -> field('task_id') ->
		 where(array('task_user_id'=>$user_id)) ->group('task_id')->select();//取出该用户发布的任务
		$a = array();
		foreach ($res1 as $key => $value) {
			$a[] = $value['task_id'];
		}
		if(empty($a)){
			$map['task_id'] = array('in',array(0));
		}else{
			$map['task_id'] = array('in',$a);
		}
		$res2 = $task->field('task_id,task_title,task_fee,fee_type,task_confirm_peo,task_join_peo,user_logo,user_nickname')
		->join('wh_user as w on w.user_id=wh_user_task.task_user_id','LEFT')->where($map)->select();
		foreach ($res2 as $key => $value) {
			$res2[$key]['task_peo_num'] = count(explode(',', $value['task_confirm_peo'])); 	
    		$res2[$key]['task_join_num'] = count(explode(',', $value['task_join_peo'])); 
    		unset($res2[$key]['task_confirm_peo']);
    		unset($res2[$key]['task_join_peo']);
		}	
    	dump($res2);  
    	$output = array('code'=>0,'data'=>array('list'=>$res2));
    	exit(json_encode($output)); 	
    }
    //我的评分(已投任务)
    public function mine_score_post(){
    	$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$taskemployer_comm = D("Taskemployer_comment");
		$task = D("User_task");
		$user = D("User");
    	/*取出用户投递任务(已评价)*/
    	$user = D("User");
		$res = $user -> field('user_id') -> where(array('user_id'=>$user_id)) -> find();
		if($res==null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output)); 
		}
    	$res1 = $taskemployer_comm -> field('task_id') ->
		 where(array('comment_user_id'=>$user_id))->select();//取出该用户投递的任务
		//dump($res3);
		$a = array();
		foreach ($res1 as $key => $value) {
			$a[] = $value['task_id'];
		}
		if(empty($a)){
			$map['task_id'] = array('in',array(0));
		}else{
			$map['task_id'] = array('in',$a);
		}
		$res2 = $task->field('task_id,task_title,task_fee,fee_type,task_confirm_peo,task_join_peo,user_logo,user_nickname')
		->join('wh_user as w on w.user_id=wh_user_task.task_user_id','LEFT')->where($map)->select();
    	foreach ($res2 as $key => $value) {
			$res2[$key]['task_peo_num'] = count(explode(',', $value['task_confirm_peo'])); 	
    		$res2[$key]['task_join_num'] = count(explode(',', $value['task_join_peo'])); 
    		unset($res2[$key]['task_confirm_peo']);
    		unset($res2[$key]['task_join_peo']);
		}
    	//dump($res4);    	
    	$output = array('code'=>0,'data'=>array('list'=>$res2));
    	exit(json_encode($output)); 	
    }
    //查看评价(已发任务)
    public function find_comment_pub(){
    	$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$task_id = $data['task_id'];
		$user = D("User");
		$taskemployee_comm = D("Taskemployee_comment");
		$res = $user -> field('user_id') -> where(array('user_id'=>$user_id)) -> find();//查询该用户是否存在
		//dump($res);
		if($res==null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output)); 
		}
		$res = $taskemployee_comm -> field('w.user_id,comment_pub_time,user_comment,user_logo,user_nickname')
		->join('wh_user as w on w.user_id=wh_taskemployee_comment.user_id')
		-> where(array('task_user_id'=>$user_id,'task_id'=>$task_id)) -> select();
		if($res == null){
			$output = array('code'=>2003,'data'=>array());
			exit(json_encode($output)); 
		}elseif(empty($res['user_comment'])){
			$res['user_comment'] = '暂无评论';
		}
		//dump($res);
		$output = array('code'=>0,'data'=>array('list'=>$res));
    	exit(json_encode($output)); 
    }
    //查看评价(已投递任务)
    public function find_comment_post(){
    	$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$task_id = $data['task_id'];
		$taskemployer_comm = D("Taskemployer_comment");
		$user = D("User");
		$res = $user -> field('user_id') -> where(array('user_id'=>$user_id)) -> find();
		if($res==null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output)); 
		}
		$res = $taskemployer_comm -> field('w.user_id,comment_pub_time,user_comment,user_logo,user_nickname')
		->join('wh_user as w on w.user_id=wh_taskemployer_comment.user_id')
		-> where(array('comment_user_id'=>$user_id,'task_id'=>$task_id)) -> find();
		if($res == null){
			$output = array('code'=>2003,'data'=>array());
			exit(json_encode($output)); 
		}elseif(empty($res['user_comment'])){
			$res['user_comment'] = '暂无评论';
		}
		dump($res);
		$output = array('code'=>0,'data'=>array('user_id'=>$res['user_id'],'comment_pub_time'=>$res['comment_pub_time'],
			'user_comment'=>$res['user_comment'],'user_logo'=>$res['user_logo'],'user_nickname'=>$res['user_nickname']));
		exit(json_encode($output)); 
    }
}