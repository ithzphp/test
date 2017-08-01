<?php
namespace Api\Controller;
use Think\Controller;
class ApiAutoRunController extends Controller{
	//定时搜索未及时评价的给雇主，并实现后续操作
	public function pro_uncomment_employer(){
		$M = M();
		$time = time();
		$sql = "call pro_uncomment_employer($time)";
		$res = $M -> execute($sql);
		//dump($res);
		//exit;
		if($res == 0){
			$task_uncomment = D("task_uncomment_record");
			$task = D("User_task");
			$employer_comm = D("Taskemployer_comment");
			$task_id = $task_uncomment -> select();
			// dump($task_id);
			if(count($task_id)==0){
				exit;
			}
			$task_ids = array_column($task_id,'task_id');
			//dump($task_ids);
			$map['task_id'] = array('in',$task_ids);
			$res = $task -> field('task_id,task_confirm_peo,task_comment_peo,task_user_id')->where($map)->select();
			foreach ($res as $key => $value) {
				$task_confirm_peo = explode(',', $value['task_confirm_peo']);
				$user_id = $value['task_user_id'];
				$task_id = $value['task_id'];
				dump($task_confirm_peo);
				// if($value['task_comment_peo'] !== null){
				// 	$task_comment_peo = explode(',', $value['task_comment_peo']);
				// }
				foreach ($task_confirm_peo as $key => $value) {
					$arr['comment_user_id'] = $value;
					$arr['conformity_score'] = 5;
					$arr['attitude_score'] = 5;
					$arr['action_capacity_score'] = 5;
					$arr['user_comment'] = '默认好评';
					$arr['user_id'] = $user_id;
					$arr['task_id'] = $task_id;
					$rules = array(array('comment_pub_time','time','1','function'));
					$employer_comm ->auto($rules) ->create($arr);
					$res = $employer_comm -> add();
					dump($res);
				}
			}
			$task_uncomment -> delete();
		}else{
			exit;
		}
	}
	//定时搜索未及时确认接单的雇员，并取消其接单
	public function pro_unconfirm_employee(){
		$time = time();
		$sql = "call pro_unconfirm_employee($time)";
		$M = M();
		$res =$M -> execute($sql);
		if($res ==0){
			$unconfirm_employee = D("Unconfirm_employee_record");
			$res = $unconfirm_employee->field('user_id,task_id,task_user_id')->select();
			if(count($res)==0){
				exit;
			}
			########通知相应雇主，该用户已放弃接单	
			$unconfirm_employee -> delete();
		}	
	}
	//定时搜索无人报名或无人接单的任务，并进行后续操作
	public function pro_no_join_confirm(){
		$M = M();
		$time = time();
		$sql = "call pro_no_join_confirm($time)";
		$res = $M -> execute($sql);
	}
	//定时搜索未及时评价的雇员
	public function pro_uncomment_employee(){
		$M = M();
		$time = time();
		$sql1 = "call pro_uncomment_employee($time)";
		$res = $M -> execute($sql1);
	}
	/////////////定时搜索未结束报名的雇主
	public function pro_unend_employe(){
		$M = M();
		$time = time();
		$sql = "call pro_unend_employe($time)";
		$res = $M -> execute($sql);
		// $sql2 = "call pro_unfinish_employee($time)";
		// $res = $M -> execute($sql2);
	}
	//定时搜索未确认交易的雇主
	public function pro_unfinish_employer(){
		$M = M();
		$time = time();
		$sql = "call pro_unfinish_employer($time)";
		$res = $M -> execute($sql);
		if($res == 0){
			$task_unfinish_record = D("Task_unfinish_record");
			$task_trace = D("User_task_trace") ;
			$user = D("User");
			$task = D("User_task");
			$res = $task_unfinish_record -> field('task_id') ->select();
			if(count($res)==0){
				exit;
			}else{
				foreach ($res as $key => $value) {
					$res1 = $task_trace -> field('user_id','task_salary','task_user_id')
					->where(array('task_id'=>$value['task_id'],'is_confirm'=>'1'))-> select();//取出该任务的接单用户
					$result = $task -> field('task_unfinish_peo,is_complain') 
					->where(array('task_id'=>$value['task_id']))->find();//取出未完成该任务的用户
					if(!empty($result['task_unfinish_peo'])){//如果未完成任务的用户不为空，则转为数组形式
						$peo = explode(',',$result['task_unfinish_peo']);
					}
					foreach ($res1 as $key => $value) {
						if(!empty($peo)){
							if(in_array($value['user_id'], $peo)){//如果该用户在未完成的用户中，则不进行报酬给予
								if($result['is_complain'] != 1){//如果该任务没有被投诉,则将未完成任务的用户酬金退还给雇主
									$fee = $value['task_salary'];
									$upd['user_coin'] = array('exp','user_coin+$fee');
									$upd['user_freeze_coin'] = array('exp','user_coin-$fee');
									$res = $user -> where(array('user_id'=>$value['task_user_id']))->save($upd);
								}
								continue;
							}
						}
						$res2 = $user -> where(array('user_id'=>$value['user_id']))->
						setInc('user_coin',$value['task_salary']);
						$res3 = $user -> where(array('user_id'=>$value['task_user_id']))
						->setDec('user_freeze_coin',$value['task_salary']);
					}
				}
			}
		}
	}
	///////////////定时搜索未确认交易的雇员
	public function pro_unfinish_employee(){
		$M = M();
		$time = time();
		$sql = "call pro_unfinish_employee($time)";
		$res = $M -> execute($sql);
	}
}