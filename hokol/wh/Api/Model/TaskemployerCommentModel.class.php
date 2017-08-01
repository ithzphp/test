<?php

namespace Api\Model;
use Think\Model;

class TaskemployerCommentModel extends Model{
	// protected $_auto = array(
 //        array('pub_time','time',1,'function'),
 //        array('upd_time','time',3,'function')
 // //    );
	// protected function _after_insert($data,$options){
	//  	$user_id = $data['comment_user_id'];
	//  	$user_credit = D('User_credit');
	//  	$info = $user_credit -> where("user_id = $user_id") -> find();
	//  	//dump($info);
	//  	//$a = 5;
	//  	if($info == null){
	//  		$arr['user_id'] = $user_id;
	//  		$arr['avg_conformity_score'] = ($data['conformity_score'] +5)/2;
	//  		$arr['avg_attitude_score'] = ($data['attitude_score'] +5)/2;
	//  		$arr['avg_action_capacity_score'] = ($data['action_capacity_score'] +5)/2;
 // 			$arr['pub_time']  =time();
	//  		$res = $user_credit ->create($arr)-> add();
	//  	}else{
	//  		$arr['avg_conformity_score'] = ($data['conformity_score'] +$info['avg_conformity_score'])/2;
	//  		$arr['avg_attitude_score'] = ($data['attitude_score'] +$info['avg_attitude_score'])/2;
	//  		$arr['avg_action_capacity_score'] = ($data['action_capacity_score'] +$info['avg_action_capacity_score'])/2;
	//  		$arr['upd_time'] = time();
	//  		//dump($arr);
	//  		$res = $user_credit -> where("user_id = $user_id")-> create($arr) ->save();
	//  	}
	//  	if($res){
	//  		$options['status'] = 1;
	//  	}else{
	//  		$options['status'] = 0;
	//  	}
	// }
}