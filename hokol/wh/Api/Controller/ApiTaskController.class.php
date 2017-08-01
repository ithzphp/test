<?php
namespace Api\Controller;
use Think\Controller;
class ApiTaskController extends Controller{
	//获取任务首页内容
	public function task_index(){
 		// $json = file_get_contents('php://input');
 		// $data = json_decode($json,true);
 		$data = I('get.');
 		$task_tag = $data['task_tag'];
 		$sex = $data['task_sex']; 		
 		$p_code = $data['p_code'];
 		$c_code = $data['c_code'];		
 		$num1 = $data['num1'];
 		$d1 = $data['length'];
 		$map  = array();
 		$region = D("Region");
 		if($sex !== 0 && $sex !== null){
 			if($sex == '1'){
 				$sex1 = '男';
 			}elseif($sex == '2'){
 				$sex1 = '女';
 			}
 			$sex1 =$sex1. ',不限';//将不限性别的任务也加进去
 		 	$map['task_sex'] = array('in',$sex1);
 		 } 		
 		if($task_tag !== 0 && $task_tag !== null){
 			$task_tag1 = $this->get_task_tag($task_tag);//返回数据的为数组
 			$task_tag1 = $task_tag1['0'];//将该数据从数组中取出
 		 	$map['_string'] = "FIND_IN_SET('$task_tag1',task_tag)";
 		 }
 		if(!empty($p_code)){
 			$task_province = $region -> field('region_name') ->where(array('region_code'=>$p_code)) ->find();
 			$task_province = $task_province['region_name'];
 			$task_province = $task_province.',不限';//将不限省份的任务也加进去
 			$map['task_province'] = array('in',$task_province);
 		}
 		//dump($task_city);
 		if(!empty($c_code)){//如果传来task_city不为空
 			$map['region_code'] = array('in',$c_code); 
 			$task_city = $region -> field('region_name') -> where($map) ->select();//根据城市码查出对应城市名
 			$task_city = array_column($task_city,'region_name');
 			$task_city[] = '不限';//将不限城市的任务也加进去(这个不限是针对发布任务者对任务执行城市的要求)
 			$map['task_city'] = array('in',$task_city);
 		}
 		$map['task_end_time'] = array('gt',time());//将过期的任务排除掉
 		//dump($map);
 		$task = D('User_task');
 		$res = $task ->	field('task_id,task_employee,task_fee,task_title,user_nickname,user_logo,task_end_time,is_guarantee')->
 		join('wh_user as w on w.user_id=wh_user_task.task_user_id','LEFT')->where($map)->limit($num1,$d1)->select();
 		//dump($res);
 		if($num1==0&&$res==0){
 			$output = array('code'=>0,'data'=>(object)array());
 			exit(json_encode($output));
 		}elseif($res){
 			foreach ($res as $key => $value) {
 				$task_employee = explode(',',$value['task_employee']);//将task_tag转为数组形式
 				$res[$key]['employee_num'] = count($task_employee);//统计报名人数
 				$time = $res[$key]['task_end_time']-time();//计算任务剩余时间
 				$res[$key]['task_rem_time'] = $time;
 				unset($res[$key]['task_end_time']);
 			}
 			//$res = array();
 			$output = array('code'=>0,'data'=>array('list'=>$res));
 			exit(json_encode($output));
 		}else{
 			$output = array('code'=>2000,'data'=>array());
 			exit(json_encode($output));
 		}	  
	}
	//发布任务
 	public function task_pub(){
 		$json = file_get_contents('php://input');
 		$data = json_decode($json,true);
 		// if(IS_POST){
 		$data = $_POST['data'];
 		$region = D("Region");
 		$task_tag = $data['task_tag'];
 		$task_tag2 = $this -> get_task_tag($task_tag);
 		//dump($task_tag2);
 		$task_tag1 = implode(',', $task_tag2);//将接收的task_tag数组改为字符串存储
 		//dump($task_tag1);
 		$data['task_tag'] = $task_tag1;//对task_tag数据重新赋值
 		if(!empty($data['p_code']){
 			$task_province = $region ->field('region_name')-> where(array('region_code'=>$data['p_code'])) ->find();
 			$task_province = $task_province['region_name'];
 		}else{
 			$task_province = '不限';
 		}
 		if(!empty($data['c_code']){
 			$task_city = $region ->field('region_name')-> where(array('region_code'=>$data['c_code'])) ->find();
 			$task_city = $task_city['region_name'];
 		}else{
 			$task_city = '不限';
 		}
 		//获取任务所需性别
 		if($data['task_man_num']!=0 && $data['task_woman_num']!=0){
 			$data['task_sex'] = '不限';
 		}elseif($data['task_man_num']!=0 && $data['task_woman_num']==0){
 			$data['task_sex'] = '男';
 		}else{
 			$data['task_sex'] = '女';
 		}
 		$task_peo_num = $data['task_man_num'] + $data['task_woman_num'];//计算任务录取总数
 		$data['task_peo_num'] = $task_peo_num;
 		$task = D('User_task');
 		if($data['task_fee'] <= 0){//如果是面议
 		    $rules = array(array('task_pub_time','time','1','function'));//构造自动添加发布时间语句
 			$data['is_guarantee'] = '0';//任务为面议，开始就是为非担保
 			$data['fee_type'] = '1';//任务的付费方式设为1(面议方式)
 			$task -> auto($rules)->create($data);//实现自动添加时间
 			$res = $task->add();
 			if($res){
 				$output = array('code'=>0,'data'=>(object)array());
 				exit(json_encode($output));
 			}
 		}else{//非面议
 			$user = D('User');
 			$user_id = $data['task_user_id'];
 			$coin = $user -> field('user_coin')->find($user_id);//统计用户的红豆数
 			$user_coin = $coin['user_coin'];//从数组中取出数据
 			$data['task_fee_total'] = $data['task_fee']*$task_peo_num;//计算任务的总费用
 			$task_coin = $data['task_fee_total']*10;//计算任务所需要的红豆数
 			$d = $user_coin-$task_coin;//比较任务所需红豆与总红豆
 			if($d < 0){//如果用户红豆数不足，则返回code：2
 				$output = array('code'=>3000,'data'=>array());
 				exit(json_encode($output));
 			}
 			//如果用户红豆数足够，将红豆费用数冻结，将user_coin下的相应数量红豆转到user_freeze_coin
 			$arr['user_coin'] = array('exp',"user_coin-$task_coin");
 			$arr['user_freeze_coin'] = array('exp',"user_freeze_coin+$task_coin");
 			$data['is_guarantee'] = '1';//如果非面议，该任务就是已担保
 			$data['fee_type'] = '0';//任务的付费方式设为0(全额担保)
 			$user->startTrans();
 			$res1 = $user -> where("user_id = $user_id") -> save($arr);
 			$rules = array(array('task_pub_time','time','1','function'));//构造自动添加发布时间语句
 			$task -> auto($rules)->create($data);//实现自动添加时间
 			$res = $task->add();
 			if($res&&$res1){
 				$user -> commit();
 				$output = array('code'=>0,'data'=>(object)array());
 				exit(json_encode($output));
 			}
 		}	
 	}
 	// }else{
 	// 		$this ->display();
 	// 	}
 	// }
	//获取任务详细信息
 	public function task_detail(){
 		$json = file_get_contents('php://input');
 		$data = json_decode($json,true);
 		//$data = I('get.');
 		$task_id = $data['task_id'];
 		$task = D('User_task');
 		$res = $task -> where(array('task_id'=>$task_id))->find();//查出该任务的具体数据
 		if($res == null){
 			$output =array('code'=>2003,'data'=>array());
 			exit(json_encode($output));
 		}elseif($res){
 			$is_join = $res['is_join'];
 			$is_finish = $res['is_finish'];
 			$is_confirm = $res['is_confirm'];
 			if($is_join == 0||$is_join == 1){
 				$res['status'] = 1;
 			}elseif($is_join == 2 && $is_confirm ==0){
 				$res['status'] = 2;
 			}elseif($is_finish == 1){
 				$res['status'] =3;
 			}
 			$task_woman_num = $res['task_peo_num']-$res['task_man_num'];
 			$res['task_woman_num'] = $task_woman_num;
 			$task_tag = explode(',',$res['task_tag']);
 			$res['task_tag'] = $task_tag;
 			$time = $res['task_end_time']-time();
 			$res['task_rem_time'] = $time;
 			unset($res['task_end_time']);
 			//dump($res);
 			$output =array('code'=>0,'data'=>array('task_id'=>$res['task_id'],'task_tag'=>$res['task_tag'],'fee_type'=>$res['fee_type'],
 				'task_fee'=>$res['task_fee'],'task_title'=>$res['task_title'],'task_content'=>$res['task_content'],'is_guarantee'=>$res['is_guarantee'],
 				'task_rem_time'=>$res['task_rem_time'],'task_pub_time'=>$res['task_pub_time'],'task_sex'=>$res['task_sex'],'status'=>$res['status'],
 				'task_peo_num'=>$res['task_peo_num'],'task_women_num'=>$res['task_woman_num'],'task_man_num'=>$res['task_man_num']));
 			exit(json_encode($output));
 		}
 	}
 	//任务报名
 	public function task_join(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		$data = I('get.');
		$user_id = $data['user_id'];
		$task_id = $data['task_id'];
		$task = D('User_task');
		$task_trace = D('User_task_trace');
		$user = D("User");
		$res = $user -> field('user_id')->where(array('user_id'=>$user_id))->find();
		if($res == null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		$info = $task_trace ->field('id')-> where(array('task_id'=>$task_id,'user_id'=>$user_id))->find();//查询是否已报名
		if($info){/*如果info不为空，则该用户已经报名该任务*/
			$output = array('code'=>3000,'data'=>array());
 			exit(json_encode($output));
		}
		$res1 = $task -> field('task_join_peo,fee_type,task_fee,task_user_id,task_end_time')->find($task_id);//查询该任务的报名者
		$task_join_peo = $res1['task_join_peo'];
		$task_user_id = $res1['task_user_id'];
		$task_end_time = $res1['task_end_time'];
		if($task_join_peo == null){//如果该任务报名者为0，直接将该用户写入数据库
			$task_join_peo = $user_id;
		}else{//如果任务录用者不为0，按规则拼接数据，更新到数据库
			$task_join_peo = $task_join_peo.','.$user_id;
		}
		$task -> startTrans();//开启事务
		$map = array('task_join_peo'=>$task_join_peo,'is_join'=>'1');//构造数据更新条件
		$res2 = $task ->where("task_id = $task_id")-> setField($map);
		if($res1['fee_type']==0){
			$arr['task_salary'] = $res1['task_fee'];//如果该任务不是面议，就把雇员任务薪水写入数据库
		}
		$arr = array('user_id'=>$user_id,
			'task_user_id'=>$task_user_id,
			'task_id'=>$task_id,
			'task_end_time'=>$task_end_time
 			);//构造添加任务跟踪表的数据
 		$rules = array(array('add_time','time','1','function'));//构造自动添加时间条件
 		$task_trace -> auto($rules)->create($arr);
 		$res3 = $task_trace -> add();//写入数据到任务跟踪表
 		if($res3 && $res2){//两个数据库操作都成功
 			$task -> commit();//事务提交
 			$output = array('code'=>0,'data'=>(object)array());
 			exit(json_encode($output));
 		}
 	}
 	//雇主对报名用户录用
	public function task_employe(){
		$json = file_get_contents("php://input");
 		$data = json_decode($json,true);
 		// $data = I('get.');
 		$user_id = $data['user_id'];//获取任务发布者ID
 		$user_id_join = $data['user_id_join'];//被录用者ID
 		$task_id = $data['task_id'];
 		$task_salary = $data['task_salary'];
 		$task = D("User_task");
 		$task_trace = D('User_task_trace');
 		$user = D("User");
 		$res1 = $user->field('user_id,user_coin')->where(array('user_id'=>$user_id))->find();//数据库查询用户是否存在
 		if($res1 == null){//返回null
 			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
 		}
 		$res2 = $task -> field('task_employee,task_peo_num') -> find($task_id);//查询该任务录用者名单
 		if($res2==null){
 			$output = array('code'=>2003,'data'=>array());
 			exit(json_encode($output));
 		}
 		$task_employee = $res2['task_employee'];//从数组中取出数据
 		$task_employees = explode(',', $task_employee);
 		$num = count($task_employees);
 		$key = in_array($user_id_join, $task_employees);//判断该用户是否报名任务
 		if($key === false){//如果该用户未报名，则无法录用
 			$output = array('code'=>3000,'data'=>array());
 			exit(json_encode($output));
 		}elseif($num==$res2['task_peo_num']){//如果该任务的确认用户量已经达到任务要求数，那么就不能再录用
 			$output = array('code'=>3000,'data'=>array());
 			exit(json_encode($output));
 		}elseif($task_employee == null){//如果该任务录用者为0，直接将该用户写入数据库
 			$task_employee = $user_id_join;
 		}else{//如果任务录用者不为0，按规则拼接数据，更新到数据库
 			$task_employee = $task_employee.','.$user_id_join;
 		}
 		if($task_salary !== null){//task_salary不为空(面议的价格提交)，判断用户红豆是否足够
 			if($res1['user_coin']-$task_salary*10<0){
 				$output = array('code'=>3000,'data'=>array());
 				exit(json_encode($output));
 			}else{
 				$add['task_employee'] = $task_employee;
 				$add['is_guarantee'] = '1';
 				$arr['is_employe'] = '1';//构造数据库更新数据
 				$arr['employe_time'] = time();
 				$arr['task_salary'] = $task_salary;

 				/*雇员消息推送(告知被录用以及录用价格)*/
 			}
 		}else{
 			$add['task_employee'] = $task_employee;
 			$arr['is_employe'] = 1;//构造数据库更新数据
 			$arr['employe_time'] = time();
 		}
 		$task -> startTrans();
 		$res3 = $task ->where("task_id = $task_id")-> save($add);//更新数据到任务数据库
 		$res4 = $task_trace -> where(array('user_id'=>$user_id_join,'task_id'=>$task_id))->save($arr);//更新数据到任务跟踪表
 		if($res4 == 0){//res4=0，说明用户未报名该任务，无权录用
 			$output = array('code'=>3000,'data'=>array());
 			exit(json_encode($output));
 		}elseif($res3 && $res4){//两个数据库操作都成功
 			$task -> commit();
 			$output = array('code'=>0,'data'=>(object)array());
 			exit(json_encode($output));
 		}
	}
	//任务报名详情
 	public function task_join_detail(){
 		$json = file_get_contents("php://input");
 		$data = json_decode($json,true);
 		// $data = I('get.');
 		$task_id = $data['task_id'];
 		$num1 = $data['num1'];
 		$d1 = $data['length'];
 		$task = D('User_task');;
 		$result = $task ->field('task_join_peo,task_employee,task_refuse_peo')-> where("task_id = $task_id") -> find();
 		if($result==null){//result为null，说明该任务不存在
 			$output = array('code'=>2003,'data'=>array());
 			exit(json_encode($output));
 		}elseif ($result['task_join_peo']==null) {//任务报名为0，则返回数据空
 			$output = array('code'=>0,'data'=>(object)array());
 			exit(json_encode($output));
 		}
 		$task_join_peo = $result['task_join_peo'];
 		$task_employee = explode(',', $result['task_employee']);//取出任务录用的人并转为数组，后面使用
 		$task_refuse_peo = explode(',', $result['task_refuse_peo']);
 		$user = D('User');
 		$map['user_id'] = array('in',$task_join_peo);//构造in查询条件
 		$res = $user -> field('user_id,user_nickname,user_logo,user_sign,user_tag')->
 		where($map)->limit($num1,$d1)->select();
 		if($res){
 			foreach ($res as $key => $value) {
 				$user_tag =explode(',', $value['user_tag']);
 				$res[$key]['user_tag'] = $user_tag;
 				$b = in_array($value['user_id'], $task_refuse_peo);
 				if($b !== false){
 					$res[$key]['is_employe'] = 2;
 				}else{
 					$a = in_array($value['user_id'], $task_employee);
 					if($a !== false){
 						$res[$key]['is_employe'] = 1;
 					}else{
 						$res[$key]['is_employe'] = 0;
 					}
 				}
 			}
 			$output = array('code'=>0,'data'=>array('list'=>$res));
 			exit(json_encode($output));
 		}else{
 			$output = array('code'=>2000,'data'=>array());
 			exit(json_encode($output));
 			}
 	}
 	//雇主取消任务
	public function cancel_task(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$task_id = $data['task_id'];
		$task = D('User_task');
		$user = D("User");
		$task_trace = D('User_task_trace');
		$info = $task -> field('task_user_id,is_confirm,task_confirm_peo,task_fee_total,task_fee') -> find($task_id);
		if($info == null){
			$output = array('code'=>2003,'data'=>array());
			exit(json_encode($output));
		}elseif($info['task_user_id'] == $user_id){
				$task -> startTrans();//开启事务
				if($info['is_confirm'] == 1){//如果该任务已经确认，不能取消
					$output = array('code'=>3000,'data'=>array());
					exit(json_encode($output));
				}elseif(!empty($task_confirm_peo)){//如果该任务已有人确认订单，取消任务需要处罚
					$task_confirm_peo = explode(',', $info['task_confirm_peo']);
					$num = count($task_confirm_peo);
					$map['user_id'] = array('in',$task_confirm_peo);
					$fine_fee = $info['task_fee']*0.05;//算出应赔付每个已接单用户的违约金
					$task_fee_total = $info['task_fee_total'];
					$res1 = $task -> where("task_id = $task_id") -> setField('is_finish','2');//task数据库更新is_finish,取消任务
					$res2 = $task_trace -> where("task_id = $task_id") -> setField('is_expire','1');//任务跟踪表更新记录
					if($res1 == 0){
						$output = array('code'=>3000,'data'=>array());
						exit(json_encode($output));
					}elseif($res2 == 0){
						$output = array('code'=>3000,'data'=>array());
						exit(json_encode($output));
					}
					$res3 = $user -> where($map) -> setInc('user_coin',$fine_fee);//给已接单的用户赔付违约金
					//$fine_coin_total = $task_fee*$num;//算出被处罚的总金额
					$rem_coin = $task_fee_total-$fine_fee*$num;//算出处罚后剩余的金额
					// dump($rem_coin);
					$arr['user_freeze_coin'] = array('exp',"user_freeze_coin-$task_fee_total");//从用户冻结金额扣除任务总费用
					$arr['user_coin'] = array('exp',"user_coin+$rem_coin");//将扣除处罚后的红豆退还至用户红豆账户
					$res4 = $user -> where("user_id = $user_id") -> save($arr);
					// dump($res4);
				}else{
					//dump($task_id);
					$res1 = $task -> where("task_id = $task_id") -> setField('is_finish',2);//task数据库更新is_finish,取消任务
					$res2 = $task_trace -> where("task_id = $task_id") -> setField('is_expire','1');//任务跟踪表更新记录
					if($res1 == 0){
						$output = array('code'=>3000,'data'=>array());
						exit(json_encode($output));
					}elseif($res2 == 0){
						$output = array('code'=>3000,'data'=>array());
						exit(json_encode($output));
					}
					$res3 = 1;
					$res4 = 1;
				}
				if($res1 && $res2 && $res3 &&$res4){
					$task -> commit();//提交事务
					$output = array('code'=>0,'data'=>(object)array());
					exit(json_encode($output));
				}
		}else{
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}	
	}
	//雇员确认、拒绝接单
	public function is_confirm_task(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];
		$task_id = $data['task_id'];
		$switchs = $data['switchs'];//接单开关，0：拒绝接单，1：确认接单
		$task_trace = D('User_task_trace');
		$task = D('User_task');
		//实现拒绝接单
		if($switchs == 0){
			$task_refuse_peo1 = $task -> field('task_refuse_peo') -> find($task_id);//取出该任务拒绝接单数据
			if($task_refuse_peo1 == null){
				$output = array('code'=>2003,'data'=>array());
				exit(json_encode($output));
			}
			$task_refuse_peo = $task_refuse_peo1['task_refuse_peo'];
			$num = count(explode(',', $task_refuse_peo));//统计数量
			if($num == 0){//若拒绝接单数为0，直接将该用户写入数据库
				$task_refuse_peo = $user_id;
			}elseif(in_array($user_id, explode(',', $task_refuse_peo))){//该用户已拒绝
				$output = array('code'=>3000,'data'=>array());
				exit(json_encode($output));
			}else{//若拒绝接单数不为0，拼接字符串，更新数据库
				$task_refuse_peo = $task_refuse_peo.','.$user_id;
			}
			$task -> startTrans();//开启事务
			$res2 = $task_trace -> where(array('task_id'=>$task_id,'user_id'=>$user_id))->setField('is_confirm','2');
			$res1 = $task -> where("task_id = $task_id") -> setField('task_refuse_peo',$task_refuse_peo);
			//dump($res2);
			if($res2 == 0){
				$task -> rollback();//回滚事务
				$output = array('code'=>3000,'data'=>array());
				exit(json_encode($output));
			}elseif($res1 && $res2){
				$task -> commit();//提交事务
				$output = array('code'=>0,'data'=>(object)array());
				exit(json_encode($output));
			}
		//实现确认接单
		}else{
			$task_confirm_peo1 = $task -> field('task_confirm_peo') ->find($task_id);
			if($task_confirm_peo1 == null){
				$output = array('code'=>2003,'data'=>array());
				exit(json_encode($output));
			}
			$task_confirm_peo = $task_confirm_peo1['task_confirm_peo'];
			$num = count(explode(',', $task_confirm_peo));//统计数量
			if($num == 0){
				$task_confirm_peo = $user_id;
			}elseif(in_array($user_id,explode(',', $task_confirm_peo))){//如果该用户已接单，则返回code2
				$output = array('code'=>3000,'data'=>array());
				exit(json_encode($output));
			}else{
				$task_confirm_peo = $task_confirm_peo.','.$user_id;
			} 
			$task -> startTrans();//开启事务
			$arr['task_confirm_peo'] = $task_confirm_peo;
			$res1 = $task -> where("task_id=$task_id") ->save($arr);
			$res2 = $task_trace -> where(array('task_id'=>$task_id,'user_id'=>$user_id))->setField('is_confirm','1');
			if($res2 == 0){
				$task -> rollback();//回滚事务
				$output = array('code'=>3000,'data'=>array());
				exit(json_encode($output));
			}elseif($res1 && $res2){
				$task -> commit();//提交事务
				$output = array('code'=>0,'data'=>(object)array());
				exit(json_encode($output));
			}
		}
	}
	//雇主结束报名
	public function end_employe(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$task_id = $data['task_id'];
		$task = D('User_task');
		$user = D("User");
		$task_trace = D('User_task_trace');
		$info = $task -> field('task_user_id,is_confirm,task_confirm_peo,task_fee_total,task_fee') -> find($task_id);
		if($info == null){
			$output = array('code'=>2003,'data'=>array());
			exit(json_encode($output));
		}
		if($info['task_user_id'] == $user_id){
				$arr['is_join'] = 2;
				$arr['is_confirm'] = 1;
				$res1 = $task -> where("task_id = $task_id") -> save($arr);//数据库更新
				if($res1 == 0){//如果res1=0，说明已经结束报名
					$output = array('code'=>3000,'data'=>array());
					exit(json_encode($output));
				}else{
					$output = array('code'=>0,'data'=>(object)array());
					exit(json_encode($output));
				}
		}else{
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}	
	}
	//雇主确定交易
	public function confirm_task(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$task_id = $data['task_id'];
		$user_id = $data['user_id'];
		$fee_type = $data['fee_type'];
		$info = $data['info'];
		$task = D("User_task");
		$task_trace = D("User_task_trace"); 
		$res = $task -> field('task_user_id,task_id')->find($task_id);
		if($res==null){
			$output = array('code'=>2003,'data'=>array());
			exit(json_encode($output));
		}elseif ($res['task_user_id'] !==$user_id) {//任务的发布者ID不是接收到的用户ID
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}
		//获取提交的确认信息，包含任务雇员user_id和完成状态confirm_status(0：未完成任务,1:完成任务)
		foreach ($info as $key => $value) {
			if($info[$key]['confirm_status']==0){
				$arr1[] = $info[$key]['confirm_user_id'];//若该用户未完成任务，记入数组arr1
			}else{
				$arr2[] = $info[$key]['confirm_user_id'];//若该用户完成任务，写入数组arr2
			}
		}
		$a = count($arr1);//统计未完成任务数量
		$b = count($arr2);
		if($a == 0){//若无未完成任务的，直接数据库将该任务记为已完成
			$res = $task -> where("task_id = $task_id")->setField('is_finish','1');			
		}else{//若存在未完成任务的用户，将他们存入数据库，并将该任务记为已完成
			$task_unfinish_peo = implode(',', $arr1);
			$res = $task -> where("task_id = $task_id")->
			setField(array('is_fnish'=>'1','task_unfinish_peo'=>$task_unfinish_peo,'is_have_unfinish'=>'1'));
			$res1 = $task_trace -> where("task_id = $task_id") -> setField('is_expire','1');//任务跟踪表更新记录
			$map['task_id'] = $task_id;
			$map['user_id'] = array('in',$arr1);
			$res2 = $task_trace -> where($map) -> setField('is_finish',3);//将未完成任务的雇员进行数据库更新 
			//******发送消息给相应的雇员，提示其未完成该任务
		}
		if($res == 0){
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}
		if($fee_type == 0){//付费方式不是面议
			$res1 = $this->distribute_fee1($task_id,$arr2);//对完成任务的雇员进行雇金分发
		}else{//付费方式为面议
			$res1 = $this->distribute_fee2($task_id,$arr2);
		}
		if($res1 && $res){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
	}
	//任务接单详情
 	public function task_confirm_detail(){
 		$json = file_get_contents("php://input");
 		$data = json_decode($json,true);
 		// $data = I('get.');
 		$num1 = $data['num1'];
 		$d1 = $data['length'];
 		$task_id = $data['task_id'];
 		$task = D('User_task');
 		$result = $task ->field('task_confirm_peo,task_comment_peo')-> where("task_id = $task_id") -> find();
 		//数据库查出该任务报名的人
 		$task_confirm_peo = $result['task_confirm_peo'];//将数据从数组中取出
 		if($result==null){
 			$output = array('code'=>2003,'data'=>array());
 			exit(json_encode($output));
 		}elseif($task_confirm_peo == null){//任务确认人数为null，则返回数据为空
 			$output = array('code'=>0,'data'=>(object)array());
 			exit(json_encode($output));
 		}
 		$user = D('User');
 		$map['user_id'] = array('in',$task_confirm_peo);
 		$res = $user -> field('user_id,user_nickname,user_logo,user_sign,user_tag')->
 		where($map)->limit($num1,$d1)->select();
 		if($res){
 			foreach ($res as $key => $value) {
 				$user_tag =explode(',', $value['user_tag']);
 				$res[$key]['user_tag'] = $user_tag;
 			}
 			$output = array('code'=>0,'data'=>array('list'=>$res));
 			exit(json_encode($output));
 		}else{
 			$output = array('code'=>2000,'data'=>array());
 			exit(json_encode($output));
 		}
	}
	//雇主评价雇员
	public function task_comment_employer(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];//雇主ID
		$task_id = $data['task_id'];
		$task = D('User_task');
		/*下面两条为测试数据*/
		//$comment_user_id = $data['comment_user_id'];
		//$attitude_score = $data['attitude_score'];
		$res = $task -> field('task_id,task_user_id')->find($task_id);//查找该任务
		if($res == null){
			$output =array('code'=>2003,'data'=>array());
		 	exit(json_encode($output));
		}elseif($res['task_user_id'] !== $user_id){
			$output =array('code'=>3000,'data'=>array());
		 	exit(json_encode($output));
		}
		$comment = $data['comment'];//获取提交的评论内容，包含被评论用户ID以及各类评分
		$arr = array();
		$employer_comm = D("Taskemployer_comment");
		$employer_comm -> startTrans();
		foreach ($comment as $key => $value) {//遍历评价信息，构造添加数据并添加入数据库
		 	$arr['comment_user_id'] = $value['comment_user_id'];
		 	$arr['conformity_score'] = $value['conformity_score'];
		 	$arr['attitude_score'] = $value['attitude_score'];
		 	$arr['action_capacity_score'] = $value['action_capacity_score'];
		 	$arr['user_comment'] = $value['user_comment'];
		 	$arr['user_id'] = $user_id;
		 	$arr['task_id'] = $task_id;
		 	$rules = array(array('comment_pub_time','time','1','function'));
		 	$employer_comm ->auto($rules) ->create($arr);
		 	$res = $employer_comm -> add();
		 } 
		 $info = $task -> field('task_comment_peo,task_confirm_peo') ->find($task_id);//查询该任务评价的用户
		 $comment_peo = $info['task_comment_peo'];
		 $task_confirm_peo = $info['task_confirm_peo'];
		 $comment_user_id = array_column('user_id',$comment);//从提交的数据中取出user_id数据
		 $task_comment_peo = implode(',', $comment_user_id);
		 if($comment_peo == null){//如果数据库中该任务的评价用户为0，将传过来的数据存入数据库
		 	$comment_peo = $task_comment_peo;
		 }else{//如果数据库中该任务的评价用户不为0，则拼接数据存库
		 	$comment_peo = $comment_peo.','.$task_comment_peo;
		 }
		 $map['task_comment_peo'] = $comment_peo;

		 /*判断此次评价后是否已完成所有评价*/
		 $num = count($comment_user_id);//计算此次评价的用户数量
		 $num1 = $this -> get_num($comment_peo);//获取已经评价的用户数量
		 $num2 = $this -> get_num($task_confirm_peo);//获取接单用户的数量
		 $a = $num1+$num;//计算此次提交后评论的总数
		 if($a == $num2){//如果评论的总数等于任务接单者人数，则就完成所有评价
		 	$map['is_comment'] = 1;//将评价状态更新为1
		 }
		 $res1 = $task -> where("task_id = $task_id") -> save($map);//对数据进行更新
		 if($res1 || $res1==0){
		 	$employer_comm -> commit();
		 	$output =array('code'=>0,'data'=>(object)array());
		 	exit(json_encode($output));
		 }
	}
	//任务的雇员数据
	public function task_comment_peo_info(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		// $data = I('get.');
		$task_id = $data['task_id'];
		$task = D('User_task');
		$user = D('User');
		$map = array();
		$info = $task -> field('task_confirm_peo,task_comment_peo')->find($task_id);
		if($info==null){
			$output = array('code'=>2003,'data'=>array());
			exit(json_encode($output));
		}
		$task_confirm_peo = $info['task_confirm_peo'];//取出该任务已经接单的用户
		$task_comment_peo = $info['task_comment_peo'];//取出已经评价的用户
		$task_comment_peo = explode(',', $task_comment_peo);
		$task_confirm_peo = explode(',', $task_confirm_peo);
		if($task_comment_peo){//若已评价的用户数据不为空，则不需要再提供这些已评价的用户信息
			foreach ($task_comment_peo as $key => $value) {//遍历已评价的用户
			$k = array_search($value, $task_confirm_peo);
			if($k !== false){
				unset($task_confirm_peo[$k]);//从已接单用户中删除已评价的用户
				}
			}
		}
		$num = count($task_confirm_peo);
		if($num == 0){//如果删除已评价的用户后，已接单的用户为空了，则说明已完成所有评价
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}	
		$map['user_id'] = array('in',$task_confirm_peo);
		$res = $user -> field('user_id,user_nickname,user_logo') ->where($map)->select();//数据库中选出被评论任务的信息
		if($res){
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}
	}
	//雇员确认交易
	public function is_success_task(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$switchs = $data['switchs'];//操作开关，0：未完成交易，1：已完成交易
		$user_id = $data['user_id'];
		$task_id = $data['task_id'];
		$task_trace = D("User_task_trace");
		if($switchs == 0){//如果用户确定未完成任务，将数据表中is_finish改为2
			$res = $task_trace -> where(array('user_id'=>$user_id,'task_id'=>$task_id))->setField('is_finish','2');
		}else{//如果用户确定完成任务，将数据表中is_finish改为1
			$res = $task_trace -> where(array('user_id'=>$user_id,'task_id'=>$task_id))->setField('is_finish','1');
		}
		if($res == 0){
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}elseif($res){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
	}
	//雇员评价交易
	public function task_comment_employee(){
		$json = file_get_contents("php://input");
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];
		$task_id = $data['task_id'];
		//$task_user_id = $data['task_user_id'];
		$employee_comm = D('Taskemployee_comment');
		$task_trace = D('User_task_trace');
		$rules = array(array('comment_pub_time','time','1','function'));
		$task_trace -> startTrans();
		$employee_comm ->auto($rules) ->create($data);
		$res = $employee_comm -> add();
		$res1 = $task_trace -> where(array('user_id'=>$user_id,'task_id'=>$task_id))
		->setField('is_finish','1');
		if($res1 == 0){
			$task_trace -> commit();
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}elseif($res1 && $res){
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}
	}
 	//获取任务的所需类型
 	public function get_task_tag($task_tag){
 		if(!is_array($task_tag)){//如果传的数据不是数组，则进行转换
 			$task_tag = explode(',', $task_tag);
 		}
 		foreach ($task_tag as $key => $value) {
 			switch ($value) {
 				case 1:
 					$task_tag[$key] = '网红';
 					break; 					
 				case 2:
 					$task_tag[$key] = '主播'; 					
 					break;
 				case 3:
 					$task_tag[$key] = '演员'; 					
 					break;
 				case 4:
 					$task_tag[$key] = '模特'; 					
 					break;
 				case 5:
 					$task_tag[$key] = '歌手'; 					
 					break;
 				default:
 					$task_tag[$key] = '体育'; 					
 					break;
 			}
		}
		return $task_tag;
 	}
 	//用户收藏、取消收藏任务
 	public function task_collect_switch(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
 		//$data = I('get.');
 		$user_id = $data['user_id'];
 		$task_id = $data['task_id'];
 		$collect = $data['collect'];//获取收藏开关，0-》取消收藏，1-》收藏任务
 		$user = D('User');
 		if($collect == 0){//实现取消收藏任务功能
 			$user_collect = $user -> field('user_collect_task') ->where(array('user_id'=>$user_id))->find();//查看用户关注的任务
 			//dump($dt_zan_people);
 			$collects = explode(',',$user_collect['user_collect_task']);
 			//dump($zan);
 			$key = array_search($task_id,$collects);//查看当前任务是否在收藏内
 			//dump($key);
 			if($key !== false){
 				unset($collects[$key]);//删除$key对应的数据，即为当前任务，实现取消收藏
 				$a = implode(',',$collects);
 				/*构造数据更新内容$data*/
 				$data['user_collect_task'] = $a;
 				$data['user_collect_task_num'] = array('exp','user_collect_task_num-1');
 				$res = $user -> where(array('user_id'=>$user_id))->save($data);//数据更新到数据库
 				if($res){
 					$output = array('code'=>0,'data'=>(object)array());
 					exit(json_encode($output));
 				}
 			}else{
 				$output = array('code'=>3000,'data'=>array());
 				exit(json_encode($output));
 			}
 		}else{//实现收藏任务功能
 			$user_collect = $user -> field('user_collect_task') ->where(array('user_id'=>$user_id))->find();//查看用户关注的任务
 			$collects = explode(',',$user_collect['user_collect_task']);
 			$key = array_search($task_id,$collects);//查看当前任务是否在收藏内
 			//dump($key);
 			if($key !== false){
 				$output = array('code'=>3000,'data'=>array());
 				exit(json_encode($output));
 			}else{
 				if($user_collect['user_collect_task'] == null){
 					$collects = $task_id;//将该条任务加入数组，实现收藏任务功能
 				}else{
 					$collects = $user_collect['user_collect_task'].','.$task_id;
 				}
 				$data['user_collect_task'] = $collects;
 				$data['user_collect_task_num'] = array('exp','user_collect_task_num+1');
 				$res = $user -> where(array('user_id'=>$user_id))->save($data);
 				if($res){
 					$output = array('code'=>0,'data'=>(object)array());
 					exit(json_encode($output));
 				}
	 		}
 		}
 	}
 	//删除任务
 	public function del_task(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$task_id = $data['task_id'];
		$task  = D("User_task");
		//$res = $task -> field('')
 	}	
 	//用户已发任务(全部)
 	public function task_published_all(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
 		// $data = I('get.');
 		$user_id = $data['user_id'];
 		$num1 = $data['num1'];
 		$d1 = $data['length'];//获取所需数据量
 		$task = D('User_task');
 		//dump($num);
 		$res = $task -> field('task_id,task_fee,task_peo_num,task_title,task_end_time,task_join_peo,task_confirm_peo,is_guarantee,
 				user_nickname,user_logo,is_confirm,is_finish,is_comment')->join('wh_user on wh_user.user_id=wh_user_task.task_user_id','LEFT')->
 				where(array('task_user_id'=>$user_id))->limit($num1,$d1)->select();
 				//dump($res);
 		if($num1==0&&count($res)==0){
 			$output = array('code'=>0,'data'=>(object)array());
 			exit(json_encode($output));
 		}elseif($res){
 			foreach ($res as $key => $value) {
 				$join_num = count(explode(',', $value['task_join_peo']));//统计报名的用户
 				$employee_num = $this -> get_num($value['task_confirm_peo']);//统计接单的用户
 				if($res[$key]['is_confirm'] == 0){
 					$res[$key]['status'] = 1;
 					$res[$key]['join_num'] = $join_num;
 					$res[$key]['employee_num'] = $employee_num;
 					$res[$key]['task_peo_num'] = $value['task_peo_num'];
 				}elseif($res[$key]['is_finish']==0 && $res[$key]['is_confirm']==1){
 					$res[$key]['status'] = 2;
 					$res[$key]['employee_num'] = $employee_num;
 					$res[$key]['task_peo_num'] = $employee_num;
 				}elseif($res[$key]['is_finish'] == 1 && $res[$key]['is_comment'] == 0){
 					$res[$key]['status'] = 3;
 					$res[$key]['employee_num'] = $employee_num;	
 					$res[$key]['task_peo_num'] = $employee_num;
 				}elseif($res[$key]['is_comment'] == 1){
 					$res[$key]['status'] = 4;
 					$res[$key]['employee_num'] = $employee_num;	
 					$res[$key]['task_peo_num'] = $employee_num;
 				}elseif($res[$key]['is_finish'] == 2){
 					$res[$key]['status'] = 5;
 					$res[$key]['task_peo_num'] = $value['task_peo_num'];
 				}
 				unset($res[$key]['is_confirm']);
 				unset($res[$key]['is_finish']);
 				unset($res[$key]['is_comment']);
 				}
 			$output = array('code'=>0,'data'=>array('list'=>$res));
 			exit(json_encode($output));
 		}else{
 			$output = array('code'=>2000,'data'=>array());
 			exit(json_encode($output));
 		}
 	}	
 	//用户已发任务(待报名)
 	public function task_published_join(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
 		// $data = I('get.');
 		$user_id = $data['user_id'];
 		$num1 = $data['num1'];
 		$d1 = $data['length'];//获取所需数据量
 		$task = D('User_task');
 		$map = array('task_user_id'=>$user_id,
 			'is_confirm'=>'0'
 			);
 		$res = $task -> field('task_id,task_fee,task_peo_num,task_title,task_end_time,task_join_peo,task_confirm_peo,is_guarantee,
 				user_nickname,user_logo')->join('wh_user on wh_user.user_id=wh_user_task.task_user_id','LEFT')->
 				where($map)->limit($num1,$d1)->select();
 		//dump($res);
 		if($num1==0&&count($res)==0){
 			$output = array('code'=>0,'data'=>(object)array());
 			exit(json_encode($output));
 		}elseif($res){
 			foreach ($res as $key => $value) {
 				$join_num = $this -> get_num($value['task_join_peo']);
 				$employee_num = $this -> get_num($value['task_confirm_peo']);
 				$res[$key]['join_num'] = $join_num;
 				$res[$key]['employee_num'] = $employee_num;
 				}
 			dump($res);
 			$output = array('code'=>0,'data'=>array('list'=>$res));
 			exit(json_encode($output));
 		}else{
 			$output = array('code'=>2000,'data'=>array());
 			exit(json_encode($output));
 		}
 	}
 	//用户已发任务(待交易)
 	public function task_published_confirm(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
 		// $data = I('get.');
 		$user_id = $data['user_id'];
 		$num1 = $data['num1'];
 		$d1 = $data['length'];//获取所需数据量
 		$task = D('User_task');
 		$map = array('task_user_id'=>$user_id,
 			'is_confirm'=>'1',
 			'is_finish'=>'0'
 			);
 		$res = $task -> field('task_id,task_fee,task_title,task_end_time,task_confirm_peo,is_guarantee,is_guarantee,
 				user_nickname,user_logo')->join('wh_user on wh_user.user_id=wh_user_task.task_user_id','LEFT')->
 				where($map)->limit($num1,$d1)->select();
 		//dump($res);
 		if($num1==0&&count($res)==0){
 			$output = array('code'=>0,'data'=>(object)array());
 			exit(json_encode($output));
 		}elseif($res){
 			foreach ($res as $key => $value) {
 				$employee_num = $this -> get_num($value['task_confirm_peo']);
 				$res[$key]['employee_num'] = $employee_num;
 				$res[$key]['task_peo_num'] = $employee_num;
 				//任务已经确定后，任务的人数应当等于最终录取的人数，而非开始要求的人数
 				}
 			dump($res);
 			$output = array('code'=>0,'data'=>array('list'=>$res));
 			exit(json_encode($output));
 		}else{
 			$output = array('code'=>2000,'data'=>array());
 			exit(json_encode($output));
 		}
 	}
 	//用户已发任务(待评价)
 	public function task_published_comment(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
 		// $data = I('get.');
 		$user_id = $data['user_id'];
 		$num1 = $data['num1'];
 		$d1 = $data['length'];//获取所需数据量
 		$task = D('User_task');
 		$map = array('task_user_id'=>$user_id,
 			'is_confirm'=>'1',
 			'is_finish'=>'0'
 			);
 		$res = $task -> field('task_id,task_fee,task_title,task_end_time,task_confirm_peo,is_guarantee,is_guarantee,
 				user_nickname,user_logo')->join('wh_user on wh_user.user_id=wh_user_task.task_user_id','LEFT')->
 				where($map)->limit($num1,$d1)->select();
 		//dump($res);
 		if($num1==0&&count($res)==0){
 			$output = array('code'=>0,'data'=>(object)array());
 			exit(json_encode($output));
 		}elseif($res){
 			foreach ($res as $key => $value) {
 				$employee_num = $this -> get_num($value['task_confirm_peo']);
 				$res[$key]['employee_num'] = $employee_num;
 				$res[$key]['task_peo_num'] = $employee_num;
 				//任务已经确定后，任务的人数应当等于最终录取的人数，而非开始要求的人数
 				}
 			dump($res);
 			$output = array('code'=>0,'data'=>array('list'=>$res));
 			exit(json_encode($output));
 		}else{
 			$output = array('code'=>2000,'data'=>array());
 			exit(json_encode($output));
 		}
 	}
	//获取数据库以','分隔的字符串数据的数量
	public function get_num($data){
		if($data == null){
			$num = 0;
		}else{
			$data = explode(',', $data);
			$num = count($data);
		}
		return $num;
	}	
	//雇主确定任务后分发雇金
	public function distribute_fee1($task_id,$confirm_peo){
		$task = D("User_task");
		$user = D("User");
		$info = $task -> field('task_user_id,task_fee')->find($task_id);
		$num = count($confirm_peo);//计算任务完成人员的数量
		$task_coin = $info['task_fee']*10;//得出每人应得的红豆
		$task_coin_total = $task_coin*$num;//得出雇主需从冻结金中扣除的红豆数
		$user_id = $info['task_user_id'];//得到雇主ID
		$map['user_id'] = array('in',$confirm_peo);//构造in语句
		$user->startTrans();
		$res1 = $user -> where("user_id = $user_id")->setDec('user_freeze_coin',$task_coin_total);
		$res2 = $user -> where($map) -> setInc('user_coin',$task_coin);
		if($res1 && $res2){
			$user -> commit();
			$res = 1;
		}else{
			$user -> rollback();
			$res = 0;
		}
		return $res;
	} 
	//雇主确定任务后分发雇金（面议任务）
	public function distribute_fee2($task_id,$confirm_peo){
		$task = D("User_task");
		$user = D("User");
		$task_trace = D("User_task_trace");
		$info = $task -> field('task_user_id,task_fee_total')->find($task_id);
		$task_coin_total = $info['task_fee_total']*10;//计算任务总红豆数
		$user_id = $info['task_user_id'];//得到雇主ID
		$map['user_id'] = array('in',$confirm_peo);//构造in语句
		$user->startTrans();
		$res1 = $user -> where("user_id = $user_id")->setDec('user_freeze_coin',$task_coin_total);
		foreach ($confirm_peo as $key => $value) {
			$task_salary = $task_trace ->field('task_salary')-> where(array('task_id'=>$task_id,'user_id'=>$value))->find();
			$task_coin = $task_salary['task_salary']*10;//计算该用户的任务报酬
			$res2 = $user -> where(array('user_id'=>$value))->setInc('user_coin',$task_coin);
		}
		if($res1 && $res2){
			$user -> commit();
			$res = 1;
		}else{
			$user -> rollback();
			$res = 0;
		}
		return $res;
	} 
	//雇员已投递任务
	public function post_task(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		// $data = I('get.');
		$user_id = $data['user_id'];
		$switchs = $data['switchs'];//选择开关(0:全部,1:已报名,2:已接单,3:待评价)
		$num1 = $data['num1'];
		$d1  = $data['length'];
		$task_trace = D('User_task_trace');
		$task = D('User_task');
		$user = D("User");
		$res =$user-> field('user_id')->find($user_id);
		if($res==null){
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}
		if($switchs == 1){
			$map = array('user_id' => $user_id,
					'is_confirm' => '0'
				);
		}elseif($switchs == 2){
			$map = array('user_id' => $user_id,
					'is_confirm' => '1',
					'is_finish' => '0'
				);
		}elseif($switchs == 3){
			$map = array('user_id' => $user_id,
					'is_finish' => '1',
					'is_comment' => '0'
				);
		}else{//别的switchs条件下取出所有数据
			$map = array('user_id' => $user_id);
		}
		//dump($map);
		$task_ids = $task_trace ->field('task_id')-> where($map) -> select();//取出数据库中满足设定条件的任务
		$task_id = array_column($task_ids,'task_id');//从二维数组中取出索引为task_id的数据
		$arr['task_id'] = array('in',$task_id);//构造in查询条件，取出满足条件的任务数据
		$res = $task -> field('wh_user_task.task_user_id,task_id,task_fee,task_title,task_end_time,task_join_peo,
 				user_nickname,user_logo')->join('wh_user on wh_user.user_id=wh_user_task.task_user_id','LEFT')
 				->where($arr)->limit($num1,$d1)->select();
 		if($res==0&&$num1==0){
 			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
 		}elseif($res){
 			foreach ($res as $key => $value) {
 				$task_id = $value['task_id'];
 				$res1 = $task_trace -> field('is_employe,is_confirm,is_finish,is_comment,is_expire')
 				->where(array('task_id'=>$task_id,'user_id'=>$user_id))->find();
 				$res[$key]['is_employe'] = $res1['is_employe'];
 				$res[$key]['is_finish'] = $res1['is_finish'];
 				$res[$key]['is_confirm'] = $res1['is_confirm'];
 				$res[$key]['is_comment'] = $res1['is_comment'];
 				$res[$key]['is_expire'] = $res1['is_expire'];
 				$a = $this -> status($res[$key]);//调用函数来确定当前任务状态
 				$res[$key] = $a;//将返回带有status的数据赋给$res[$key],
 				$join_peo = explode(',', $a['task_join_peo']);
 				$join_num = count($join_peo);
 				$res[$key]['join_num'] = $join_num;
 				unset($res[$key]['task_join_peo']);
 				}
 			$output = array('code'=>0,'data'=>array('list'=>$res));
 			exit(json_encode($output));
 		}else{
 			$output = array('code'=>2000,'data'=>array());
 			exit(json_encode($output));
 		}
	}
	//雇员任务状态确定函数
	public function status($data){
		$is_employe = $data['is_employe'];
		$is_confirm = $data['is_confirm'];
		$is_finish = $data['is_finish'];
		$is_comment = $data['is_employe'];
		$is_expire = $data['is_expire'];
		if ($is_expire==1&&$is_employe==0) {
			$data['status'] = 9;
		}elseif($is_employe == 0){
			$data['status'] = 1;
		}elseif ($is_employe==1 && $is_confirm==0) {
			$data['status'] = 2;
		}elseif ($is_confirm ==2&&$is_finish==0) {
			$data['status'] = 3;
		}elseif ($is_finish==0 && $is_confirm==1) {
			$data['status'] = 4;
		}elseif ($is_finish==3) {
			$data['status'] = 5;
		}elseif ($is_finish==2 ) {
			$data['status'] = 6;
		}elseif ($is_finish==1 && $is_comment==0) {
			$data['status'] = 7;
		}elseif ($is_comment==1) {
			$data['status'] = 8;
		}
		unset($data['is_employe']);
		unset($data['is_finish']);
		unset($data['is_comment']);
		unset($data['is_expire']);
		unset($data['is_confirm']);
		return $data;
	}
	//雇主提交面议价格
	// public function commit_task_fee(){
	// 	$json = file_get_contents("php://input");
	// 	$data = json_decode($json,true);
	// 	//$data = I("get.");
	// 	$task_trace = D("User_task_trace");
	// 	$task = D("User_task");
	// 	$user = D("User");
	// 	$info = $data['info'];
	// 	$task_id = $data['task_id'];
	// 	$user_id = $data['user_id'];
	// 	$task_fee_total = 0;//记录任务的总费用(单位为rmb)
	// 	$user->startTrans();
	// 	foreach ($info as $key => $value) {
	// 		$arr['task_salary'] = $value;
	// 		if($value<=0){//如果雇员任务费用小于等于0，则不合法
	// 			$output = array('code'=>3000,'data'=>array());
	// 			exit(json_encode($output));
	// 		}
	// 	  	$res = $task_trace ->where(array('user_id'=>$key,'task_id'=>$task_id))-> save($arr);//将雇员的任务费用记录
	// 		$task_fee_total = $task_fee_total + $value;
	// 	}
	// 	$task_coin = $task_fee_total*10;//任务总费用(单位为红豆)
	// 	$user_coin = $user->field('user_coin')->where(array('user_id'=>$user_id))->find();//查询该雇主的红豆
	// 	if($user_coin==null){//没有数据，该用户不存在
	// 		$output = array('code'=>2001,'data'=>array());
	// 		exit(json_encode($output));
	// 	}elseif($user_coin['user_coin'] < $task_coin){//用户红豆数小于总费用
	// 		$output = array('code'=>3000,'data'=>array());
	// 		exit(json_encode($output));
	// 	}else{
	// 		$arr['user_coin'] = array('exp',"user_coin-$task_coin");
 // 			$arr['user_freeze_coin'] = array('exp',"user_freeze_coin+$task_coin");
 // 			$res1 = $user -> where("user_id = $user_id") -> save($arr);
	// 		$res2 = $task -> where(array('task_id'=>$task_id))
	// 		->setField(array('task_fee_total'=>$task_fee_total));//将任务的总费用记录到数据库
	// 		if($res2 && $res1){
	// 			$user -> commit();
	// 			$output = array('code'=>0,'data'=>(object)array());
	// 			exit(json_encode($output));
	// 		}		
	// 	}		
	// }
	//雇员进行维权投诉
	public function employee_complain(){
		// $json = file_get_contents("php://input");
		// $data = json_decode($json,true);
		$data = I("get.");
		$task_id = $data['task_id'];
		$user_id = $data['user_id'];
		$task = D("User_task");
		$employee_complain = D("Employee_complain");
		$task_trace = D("User_task_trace");
		$task_confirm_peo = $task -> field('task_confirm_peo') -> where(array('task_id'=>$task_id)) ->find();//查询该任务的接单用户
		//dump($task_confirm_peo);
		if($task_confirm_peo == null){//返回null即为任务不存在
			$output = array('code'=>2003,'data'=>array());
 			exit(json_encode($output));
		}else{
			$task_confirm_peo = explode(',', $task_confirm_peo['task_confirm_peo']);
			if(!in_array($user_id, $task_confirm_peo)){
				$output = array('code'=>3000,'data'=>array());
 				exit(json_encode($output));
			}
		}
		$res = $employee_complain -> field('id') -> where(array('user_id'=>$user_id))->find();
		if($res == null){
			$add['user_id'] = $user_id;
			$add['task_id'] = $task_id;
			$add['add_time'] = time();
			$task ->startTrans();
			$employee_complain ->create($add);
			$res1 = $employee_complain -> add();
			$res2 = $task -> where(array('task_id'=>$task_id))->setField('is_complain','1');
			$res3 = $task_trace -> where(array('task_id'=>$task_id,'user_id'=>$user_id))->setField('is_appeal','1');
			if($res1 && $res2 && $res3){
				$task -> commit();
				$output = array('code'=>0,'data'=>(object)array());
 				exit(json_encode($output));
			}
		}else{
			$output = array('code'=>3000,'data'=>array());
 			exit(json_encode($output));
		}
	}
	//任务推荐
	public function task_recommend(){
 		// $data = I('get.');
 		$task_rec = D("Task_recommend");
 		$res = $task_rec ->field('banner_img,rec_info,type')->limit(3)->select();
 		$output = array('code'=>0,'data'=>array('list'=>$res));
 		exit(json_encode($output));
	}
}