<?php
namespace Back\Controller;
use Think\Controller;

class JsonController extends Controller{
	public function register(){
		dump(time());
		$output = array();

		//获取客户端提交的注册信息
		$json = file_get_contents('php//input');
		$data = json_decode($json,true);	
		$user_tel = $data['$user_tel'];
		//数据库查询信息
		$user = D('User');
		$res = $user -> where(array('user_tel' => $user_tel))->find();
		if($res){
			$output = array('code'=>0);
			exit(json_encode($output));	
		}else{
			$user -> add($data);
			$output = array('code'=>1);
			exit(json_encode($output));
		}
	}
	public function login(){	
		$output = array();
		//获取客户端提交的登录信息
		$json = file_get_contents('php//input');
		$data = json_decode($json,true);
		$user = D('User');
			//数据库验证登录信息
			$user_pwd = $data['user_pwd'];
			$user_tel = $data['user_tel'];
			//dump($data);
			$res = $user -> where(array('user_tel'=>$user_tel,'user_pwd'=>$user_pwd))
			->find();
			dump($res);
			if($res){
				$output = array('code'=>1,'data'=>
				array('user_tel'=>$res['user_tel'].'用户手机','user_pwd'=>$res['user_pwd'].'用户密码','user_nickname'=>$res['user_nickname'].'用户昵称',
					'user_province'=>$res['user_province'].'用户省份','user_id'=>$res['user_id'].'用户ID，唯一标识','user_city'=>$res['user_city'].'用户城市',
					'user_weixin'=>$res['user_weixin'].'用户微信','user_weixin_id'=>$res['user_weixin_id'].'微信ID标识','user_sex'=>$res['user_sex'].'用户性别','user_logo'=>$res['user_logo']));
			exit(json_encode($output));	
			}else{
				$output = array('code'=>0);
			}
	}
	public function login1(){	
		if(IS_POST){
			$output = array();
			$data = array();
			$data['token'] = $_POST['token'];
			$data['user_pwd'] = $_POST['user_pwd'];
			$data['user_tel']= $_POST['user_tel'];
			dump($data['token']);

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
				$token = session_id();//生成token,并赋值为session_id
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
	public function pro(){
		/*雇员不接单*/
		BEGIN
		DECLARE stop,n_d,n_user_id,n_task_id,n_employe_time int default 0;
		DECLARE n_task_refuse_peo,n_task_refuse_peo1 varchar(512);
		DECLARE cur cursor for select user_id,task_id,employe_time from wh_user_task_trace where is_confirm='0' AND is_employe='1';
		DECLARE continue handler for not found SET stop=1;
		open cur;
		repeat
		fetch cur into n_user_id,n_task_id,n_employe_time;
		if not stop then
			SET n_d=n_time-n_employe_time;
		if n_d>7200 THEN
			insert into wh_task_record(user_id,task_id)values(n_user_id,n_task_id);
		update wh_user_task_trace set is_refuse='1' where user_id=n_user_id AND task_id=n_task_id;
		SET n_task_refuse_peo=(SELECT task_refuse_peo FROM wh_user_task WHERE task_id=n_task_id);
		if n_task_refuse_peo='' THEN
			set n_task_refuse_peo1=n_user_id;
		ELSE
		set n_task_refuse_peo1=CONCAT(n_task_refuse_peo,',',n_user_id);
		end if;
		update wh_user_task set task_refuse_peo=n_task_refuse_peo1 WHERE task_id=n_task_id;
		end if;
		end if;
		until stop end repeat; 
		close cur;
		END
 		/*雇员不确认交易*/
 		create PROCEDURE pro_unfinish_employee(in n_time int)
 		BEGIN
 			DECLARE stop,n_user_id,n_task_id,n_task_end_time,n_d int default 0;
 			DECLARE cur cursor for select user_id,task_id,task_end_time from wh_user_task_trace where is_confirm='1' AND is_finish='0';
 			DECLARE continue handler for not found SET stop=1;
 			open cur;
 			repeat
 			fetch cur into n_user_id,n_task_id,n_task_end_time;
 			if not stop then
 				set n_d=n_time-n_task_end_time;
 				if d>864000 then
 					update wh_user_task_trace set is_finish='1' where user_id=n_user_id AND task_id=n_task_id;
 				end if;
 			end if;
 			until stop end repeat;
 			close cur;
 		END
	/*雇员不按时评价*/
	create PROCEDURE pro_uncomment_employee(in n_time int)
	BEGIN
		DECLARE stop,n_user_id,n_task_id,n_task_user_id,n_task_end_time,n_d int default 0;
		DECLARE cur cursor for select user_id,task_id,task_user_id,task_end_time from wh_user_task_trace where is_finish='1' AND is_comment='0';
		DECLARE continue handler for not found SET stop=1;
		open cur;
		repeat
		fetch cur into n_user_id,n_task_id,n_task_user_id,n_task_end_time;
		if not stop then
			set n_d=n_time-n_task_end_time;
			if n_d>864000 then
				update wh_user_task_trace set is_comment='1';
				insert into wh_taskemployee_comment(user_id,task_id,task_user_id,conformity_score,action_capacity_score,attitude_score,conformity_comment,action_capacity_comment,attitude_comment,comment_pub_time)values(n_user_id,n_task_id,n_task_user_id,5,5,5,'系统好评','系统好评','系统好评',n_time);
			end if;
		end if;
		until stop end repeat;
	END
	/*雇主不确认订单*/
	BEGIN
 		DECLARE stop,n_task_id,n_task_user_id,n_task_end_time,n_d,n_task_confirm_peo,n_task_confirm_num,n_task_fee,n_task_fee_total,n_fee int default 0;
 		#DECLARE n_task_confirm_peo varchar(512); 
 		DECLARE cur cursor for select task_user_id,task_id,task_end_time,task_confirm_peo,task_fee,task_fee_total,task_confirm_num from wh_user_task where is_confirm='1' AND is_finish='0';
 		DECLARE continue handler for not found SET stop=1;
 		open cur;
 		repeat
 		fetch cur into n_task_user_id,n_task_id,n_task_end_time,n_task_confirm_peo,n_task_fee,n_task_fee_total,n_task_confirm_num;
 		if not stop then
 			set n_d=n_time-n_task_end_time;
			set n_fee=n_task_fee_total-n_task_fee*n_task_confirm_num;
 			if d>864000 then
 				update wh_user_task set is_finish='1' where task_id=n_task_id;
 				update wh_user set user_coin=user_coin+n_task_fee where find_in_set(user_id,n_task_confirm_peo);
 				update wh_user set user_freeze_coin=user_freeze_coin-(n_task_fee*n_task_confirm_num),user_coin=user_coin+n_fee where user_id=n_task_user_id;
 			end if;
 		end if;
 		until stop end repeat;
 		close cur;
 	/*雇主不评价*/
 	create PROCEDURE pro_uncomment_employer(in n_time int)
 	BEGIN
 		DECLARE stop,n_user_id,n_task_id,n_task_end_time,n_d int default 0;
 		#DECLARE n_task_comment_peo,n_task_unfinish_peo varchar(512); 
 		DECLARE cur cursor for select user_id,task_id,task_end_tme from wh_user_task where is_finish='1' AND is_comment='0';
 		DECLARE continue handler for not found SET stop=1;
 		open cur;
 		repeat
 		fetch cur into n_user_id,n_task_id,n_task_end_time;
 		if not stop then
 			set n_d=n_time-n_task_end_time;
 			if n_d>864000 then
 				update wh_user_task set is_comment='1' where task_id=n_task_id;
 				#insert into wh_taskemployer_comment(user_id,task_id,comment_user_id,conformity_score,action_capacity_score,attitude_score,conformity_comment,action_capacity_comment,attitude_comment,comment_pub_time)values(n_user_id,n_task_id,) 
 				insert into wh_task_trans(task_id) values(n_task_id);
 			end if;
 		end if;
 		until stop end repeat;
 		close cur;
 	END
 	/*雇主不结束报名*/
 	create PROCEDURE pro_unend_employe(in n_time int)
 	BEGIN
 	 	DECLARE stop,n_task_id,n_task_end_time,n_d int default 0;
 		DECLARE cur cursor for select task_id,task_end_time from wh_user_task where employe_confirm='1' AND is_confirm='0';
 		DECLARE continue handler for not found SET stop=1;
 		open cur;
 		repeat
 		fetch cur into n_task_id,n_task_end_time;
 		if not stop then
 			set n_d=n_time-n_task_end_time; 
 			if n_d>864000 then
 				update wh_user_task set is_join='2',is_confirm='1' where task_id=n_task_id;
 			end if;
 		end if;
 		until stop end repeat;
 		close cur;
 	END 
 	/*任务无人报名*/
 	create PROCEDURE pro_no_join(in n_time int)
 	BEGIN
 		DECLARE stop,n_task_id,n_task_end_time,n_d int default 0;
 		DECLARE cur cursor for select task_id,task_end_time from wh_user_task where is_join='0' or employe_confirm='0';
 		DECLARE continue handler for not found SET stop=1;
 		open cur;
 		repeat
 		fetch cur into n_task_id,n_task_end_time;
 		if not stop then
 			set n_d=n_time-n_task_end_time;
 			if n_d<864000 then
 				update wh_user_task set is_finish='2' where task_id=n_task_id;
 			end if;
 		end if;
 		until stop end repeat;
 	END 
 }

}