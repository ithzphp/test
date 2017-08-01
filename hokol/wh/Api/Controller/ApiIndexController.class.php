<?php
namespace Api\Controller;
use Think\Controller;
class ApiIndexController extends Controller{
	//获取主页信息
	public function index(){
		// $json = file_get_contents('php://input');
		// $data = json_decode($json,true);
 		$data = I('get.');
 		$dt = D('Dongtai');
 		$region = D('Region');
 		$dt_pics = D('Dt_pics');
 		$user = D('User');
 		$region = D("Region");
 		$num1 = $data['num1'];
 		$d1 = $data['length'];
 		$user_tag1 = $data['user_tag'];
 		//对用户的标签进行转换
 		if($user_tag1){
 			$user_tag = $this -> get_user_tag($user_tag1);
 		}	
 		$p_code = $data['p_code'];
 		$c_code = $data['c_code'];
 		$user_sex  = $data['user_sex'];
 		//对用户性别进行转换
 		if($user_sex == 1){
 			$user_sex1 = '男';
 		}elseif($user_sex == 2){
 			$user_sex1 = '女';
 		}
 		//dump($user_sex);
 		$user_adv  = $data['user_adv'];//获取主页推荐（1：人气:，2：最新）
 		$where = "1";//构造where初始条件
 		//对省份，城市，性别进行判断，构造where语句
 		if(!empty($p_code)){
 			$user_province = $region -> field('region_name') ->where(array('region_code'=>$p_code)) ->find();
 			$user_province = $user_province['region_name'];
 			$where = $where." AND d.user_province = '$user_province'";
 		}
 		if($user_sex !== 0 && $user_sex !== null){
 			$where = $where." AND d.user_sex = '$user_sex1'";
 		} 
 		if(!empty($c_code)){
 			$map['region_code'] = array('in',$c_code); 
 			$user_city = $region -> field('region_name') -> where($map) ->select();//根据城市码查出对应城市名
 			$user_city = array_column($user_city,'region_name');
 			$user_city1 = '(';
 			foreach ($user_city as $key => $value) {
 				$user_city1 = $user_city1.'"'.$value.'"'.',';
 			}
 			$user_city1 = substr($user_city1, 0,-1);
 			$user_city1 = $user_city1.')';
			$where = $where." AND d.user_city in $user_city1";
		}
 		//dump($where);
 		if($user_adv == 1){//用户推荐为人气
 			//构造查询sql语句，通过联表以及子查询找出数据
 					$sql = "select * from (select * from (select b.dt_id,user_id,user_sex,
 				user_province,user_tag,user_city,user_nickname,dt_total_zan,
 				dt_img from wh_dongtai as b left join wh_dt_pics pic on 
 				b.dt_id=pic.dt_id join wh_user a on b.dt_user_id=a.user_id GROUP BY user_id,
 				dt_total_zan desc) as c GROUP BY user_id) as d where $where AND 
 				FIND_IN_SET('$user_tag',user_tag) limit $num1,$d1";//构造查询sql语句，通过联表以及子查询找出数据

 			 	$res = $dt ->query($sql);//数据库查询数据
 			 	if($num1==0&&$res==null){
 			 		$output = array('code'=>0,'data'=>(object)array());
 			 		exit(json_encode($output));
 			 	}elseif($res) {
 			 		foreach ($res as $key => $value) {
 			 			$p_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_province'])) ->find();
 			 			$c_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_city'])) ->find();
 			 			$res[$key]['province'] = array($value['user_province'],$p_code['region_code']);
 			 			$res[$key]['city'] = array($value['user_city'],$c_code['region_code']);
						unset($res[$key]['user_province']);//删除数组键
						unset($res[$key]['user_city']);//删除数组键
					}
 			 		$output = array('code'=>0,'data'=>array('list'=>$res));
 			 		exit(json_encode($output));
 			 	}else{
 			 		$output = array('code'=>2000,'data'=>array());
 			 		exit(json_encode($output));
 			 	}
 			}
 		elseif($user_adv == 2){
					$sql = "select * from (select * from (select b.dt_id,user_id,user_sex,
 				user_province,user_tag,user_city,user_nickname,dt_pub_time,
 				dt_img from wh_dongtai as b left join wh_dt_pics pic on b.dt_id=pic.dt_id 
 				join wh_user a on b.dt_user_id=a.user_id GROUP BY user_id,dt_pub_time desc)
 				 as c GROUP BY user_id) as d where $where 
				AND FIND_IN_SET('$user_tag',user_tag) limit $num1,$d1";
 			 	$res = $dt ->query($sql);
 			 	if($num1==0&&$res==null){
 			 		$output = array('code'=>0,'data'=>(object)array());
 			 		exit(json_encode($output));
 			 	}elseif($res) {
 			 		foreach ($res as $key => $value) {
 			 			$p_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_province'])) ->find();
 			 			$c_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_city'])) ->find();
 			 			$res[$key]['province'] = array($value['user_province'],$p_code['region_code']);
 			 			$res[$key]['city'] = array($value['user_city'],$c_code['region_code']);
						unset($res[$key]['user_province']);//删除数组键
						unset($res[$key]['user_city']);//删除数组键
					}
 			 		$output = array('code'=>0,'data'=>array('list'=>$res));
 			 		exit(json_encode($output));
 			 	}else{
 			 		$output = array('code'=>2000,'data'=>array());
 			 		exit(json_encode($output));
 			 	}
 			}
 		else{
 					$sql = "select * from (select * from (select b.dt_id,user_id,user_sex,
 					user_province,user_tag,user_city,user_nickname,dt_pub_time,dt_img from 
 					wh_dongtai as b left join wh_dt_pics pic on b.dt_id=pic.dt_id join wh_user 
 					a on b.dt_user_id=a.user_id GROUP BY user_id) as c GROUP BY user_id) as 
					d where $where AND FIND_IN_SET('$user_tag',user_tag) limit $num1,$d1";

 			 	$res = $dt ->query($sql);
 			 	if($num1==0&&$res==null){
 			 		$output = array('code'=>0,'data'=>(object)array());
 			 		exit(json_encode($output));
 			 	}elseif($res) {
 			 		foreach ($res as $key => $value) {
 			 			$p_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_province'])) ->find();
 			 			$c_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_city'])) ->find();
 			 			$res[$key]['province'] = array($value['user_province'],$p_code['region_code']);
 			 			$res[$key]['city'] = array($value['user_city'],$c_code['region_code']);
						unset($res[$key]['user_province']);//删除数组键
						unset($res[$key]['user_city']);//删除数组键
					}
 			 		$output = array('code'=>0,'data'=>array('list'=>$res));
 			 		exit(json_encode($output));
 			 	}else{
 			 		$output = array('code'=>2000,'data'=>array());
 			 		exit(json_encode($output));
 			 	}
 			}
 	}
 	public function get_user_tag($tag){
 		switch ($tag) {
 				case 1:
 					$user_tag = '网红';
 					break; 					
 				case 2:
 					$user_tag = '主播'; 					
 					break;
 				case 3:
 					$user_tag = '演员'; 					
 					break;
 				case 4:
 					$user_tag = '模特'; 					
 					break;
 				case 5:
 					$user_tag = '歌手'; 					
 					break;
 				default:
 					$user_tag = '体育'; 					
 					break;
 			}
 		return $user_tag;
 	}
 	//主页推荐
 	public function index_recommend(){
 		$index_rec = D("Index_recommend");
 		$res = $index_rec ->field('banner_img,rec_info,type')->limit(3)->select();
 		$output = array('code'=>0,'data'=>array('list'=>$res));
 		exit(json_encode($output));
 	}
}