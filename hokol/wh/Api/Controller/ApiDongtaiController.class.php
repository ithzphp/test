<?php
namespace Api\Controller;
use Think\Controller;
class ApiDongtaiController extends Controller{

	//获取所有关注的人的多条动态
	public function dongtai(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		// $data = I('get.');
		$region = D("Region");
		$num1 = $data['num1'];
		$d1 = $data['length'];//获取要请求的数据量
		$user_id = $data['user_id'];
		$user = D('User');
		$dt   = D("Dongtai");
		$dt_img   = D("Dt_pics");
		$map = array();
		$user_care = $user -> where(array('user_id'=>$user_id))->
		field('user_care')->find();//获取用户关注的人（数组）
		if($user_care == null){//返回数据为null，该用户不存在
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}elseif($user_care['user_care'] == null){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
		$care1 =$user_care['user_care'];//从数组中取出关注的人（字符串）
		$care = explode(',',$care1);//将字符串改为数组形式
		$map['user_id'] = array('in',$care);//构造in查询语句
		$res = $user ->field('dt_id,user_id,user_logo,user_tag,dt_total_zan,user_city,user_province,user_nickname,user_coin,dt_user_id,dt_pub_time,dt_content,dt_zan_people')
		->join('wh_dongtai on wh_user.user_id=wh_dongtai.dt_user_id')->where($map)->limit("$num1,$d1")->select();
		//数据库联表查询信息
		if($num1==0 && $res==null){//如果$num1=0并且$res=null,则说明关注的人的动态量为0
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}elseif($res){                                                                                                                                                                                                                            
			foreach ($res as $key => $value) {
				$zan = explode(',',$value['dt_zan_people']);
				$tag = explode(',',$value['user_tag']);
			//对标签以及点赞的人进行处理
				$map = array(); 
				$map['user_id'] = array('in',$zan);//构造in查询语句
				$user_nickname = $user -> field('user_nickname')->where($map)->select();
				$user_nicknames=array_column($user_nickname,'user_nickname');//二维数组转为一维数组
				//dump($res[$key]['dt_id']);
				$dt_imgs = $dt_img ->field('dt_img')->where(array('dt_id'=>$res[$key]['dt_id']))->find();
				$dt_imgs1 = $dt_imgs['dt_img'];//从数组中取出图片数据
				$res[$key]['user_tag'] =$tag;//将用户标签加入数组
				$res[$key]['dt_zan_people_nickname'] = $user_nicknames;
				$res[$key]['dt_img'] = $dt_imgs1;//将动态的图片加入数组
				//dump($value);
				$p_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_province'])) ->find();
				$c_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_city'])) ->find();
				$res[$key]['province'] = array($value['user_province'],$p_code['region_code']);
				$res[$key]['city'] = array($value['user_city'],$c_code['region_code']);
				unset($res[$key]['dt_zan_people']);//删除数组键
				unset($res[$key]['user_province']);//删除数组键
				unset($res[$key]['user_city']);//删除数组键
				unset($res[$key]['user_id']);
			}
				//dump($res);
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}
		else{
			$output = array('code'=>2000,'data'=>array());
			exit(json_encode($output));
		}
	}
	//获取单条动态信息
	public function dt_one(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
 		// $data = I('get.');
 		$dt_id = $data['dt_id'];
 		$user_id = $data['user_id'];
 		$dt_pics = D('Dt_pics');
 		$dt = D('Dongtai');
 		$res = $dt -> field('user_nickname,user_coin,user_logo,dt_user_id,dt_pub_time,dt_total_zan,dt_content,dt_zan_people,dt_img')->
 		join('wh_user on wh_user.user_id=wh_dongtai.dt_user_id')->join('wh_dt_pics as pic on pic.dt_id=wh_dongtai.dt_id','LEFT')->where(array('wh_dongtai.dt_id'=>$dt_id))->find();//从数据库查出所需信息
 		//$num = count($res['dt_user_id']);
 		if($res == null){
 			$output = array('code'=>2002,'data'=>array());
 				exit(json_encode($output));
 		}elseif($res){
 			$user = D('User');
			$zan = explode(',',$res['dt_zan_people']);//将该字段的字符串数据改为数组
			if(in_array($user_id, $zan)){//判断该用户是否赞过此动态
				$res['is_zan'] = 1;
			}else{
				$res['is_zan'] = 0;
			}
			$user_fans = $user -> field('user_fans') -> where(array('user_id'=>$res['dt_user_id'])) ->find();
			if(in_array($user_id,explode(',',$user_fans['user_fans']))){
				$res['is_care'] = 1;
			}else{
				$res['is_care'] = 0;
			}
			$map = array();
			$map['user_id'] = array('in',$zan);//构建in条件，user_id in $zan 
			$user_nickname = $user -> field('user_nickname')->where($map)->select();
			$user_nicknames=array_column($user_nickname,'user_nickname');//取出数组中的某一列数据
			$res['dt_zan_people_nickname'] = $user_nicknames;//在结果中添加dt_zan_people_nickname数据
			unset($res['dt_zan_people']);
 			$output = array('code'=>0,'data'=>array('user_nickname'=>$res['user_nickname'],'user_coin'=>$res['user_coin'],
 			'user_logo'=>$res['user_logo'],'dt_user_id'=>$res['dt_user_id'],'dt_pub_time'=>$res['dt_pub_time'],'dt_total_zan'=>$res['dt_total_zan'],
 			'dt_content'=>$res['dt_content'],'dt_zan_people_nickname'=>$res['dt_zan_people_nickname'],'dt_img'=>$res['dt_img'],'is_care'=>$res['is_care'],'is_zan'=>$res['is_zan']));
 			exit(json_encode($output));			
		}	
 	}
 	//获取用户的多条动态
 	public function dt_nums(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
 		// $data = I('get.');
 		$num1 = $data['num1'];
 		$d1 = $data['length'];
 		$user_id = $data['user_id'];
 		$region = D("Region");
 		$dt   = D('Dongtai');
 		$user = D('User');
 		$res1 = $user -> field('user_id')->where("user_id=$user_id")->find();//检验该用户是否存在
 		if($res1 == null){
 			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
 		}
 		$res = $dt ->field('wh_dongtai.dt_id,dt_small_img,dt_total_zan,dt_zan_people,
 			wh_user.user_id,user_nickname,user_logo,user_coin,user_city,user_province,dt_content,dt_pub_time')
 		->join('wh_dt_pics as dp on dp.dt_id=wh_dongtai.dt_id','LEFT')
 		->join('wh_user on wh_user.user_id=wh_dongtai.dt_user_id')
 			->where(array('dt_user_id'=>$user_id))->limit($num1,$d1)->select();//数据库查询数据
 			//dump($res);
 		if($num1==0 && $res==null){
 			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
 		}elseif($res){
 			foreach ($res as $key => $value) {
 				$zan = explode(',',$value['dt_zan_people']);
				//对标签以及点赞的人进行处理
 				$map = array();
				$map['user_id'] = array('in',$zan);//构造in查询语句
				$user_nickname = $user -> field('user_nickname')->where($map)->select();
				$user_nicknames=array_column($user_nickname,'user_nickname');//二维数组转为一维数组
				$res[$key]['dt_zan_people_nickname'] = $user_nicknames;
				$p_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_province'])) ->find();
				$c_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_city'])) ->find();
				$res[$key]['province'] = array($value['user_province'],$p_code['region_code']);
				$res[$key]['city'] = array($value['user_city'],$c_code['region_code']);
				unset($res[$key]['dt_zan_people']);//删除数组键
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
 	//获取用户的多条私密动态
 	public function dt_private_nums(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
 		//$data = I('get.');
 		$num1 = $data['num1'];
 		$d1 = $data['length'];
 		$user_id = $data['user_id'];
 		$user_id_find = $data['user_id_find'];//被查询的用户
 		$pri   = D('Private_space');
 		$user = D('User');
 		$user_memb = D("User_member");
 		$once_space = D("Recharge_once_space");
 		$res1 = $user->field('user_see_card')->where(array('user_id'=>$user_id))->find();//数据库查询用户是否存在并取出空间查看卡
 		//dump($res1);
 		if($res1==null){
 			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
 		}
 		if($user_id !== $user_id_find){//用户查看的不是自己的私密空间
 			$res = $user_memb -> field('expire_time')->where(array('user_id'=>$user_id))->find();
 			//dump($res);
 			if($res == null){//如果没有数据(不是会员)
 				$res2 = $once_space -> field('expire_time')->
 				where(array('user_id'=>$user_id,'user_id_find'=>$user_id_find))->find();//查看用户是否买了该用户的一天私密空间查看权
 				//dump($res2);
 				$user -> startTrans();//开启事务
 				if($res2 == null){
 					if($res1['user_see_card'] != 0){//查看该用户是否有私密空间查看卡
 						$result =1;
 						/*如果用户通过空间查看卡查看私密空间，被查看的用户应得到的分成*/
 						//$award = 20*0.7;//被查看私密空间用户得到的分成(暂不分成)
 						$result = $user -> where(array('user_id'=>$user_id_find))->setInc('user_coin',20);//更新被查看私密空间用户的红豆
 						$arr['user_id'] = $user_id;
 						$arr['user_id_find'] = $user_id_find;
 						$arr['expire_time'] = time()+86400;
 						$arr['add_time'] = time();
 						$res3 = $once_space -> add($arr);//添加数据到私密空间查看表
 						$res4 = $user -> where(array('user_id'=>$user_id))->setDec('user_see_card',1);
 					}else{
 						$output = array('code'=>3000,'data'=>array());
						exit(json_encode($output));
 					}
 				}elseif(time()-$res2['expire_time']<0){
 					$result = 1;
 				}else{
 					if($res1['user_see_card'] != 0){
 						$result =1;
 						$arr['expire_time'] = time()+86400;
 						$res3 = $once_space ->save($arr);//更新私密空间查看表
 						$res4 = $user -> where(array('user_id'=>$user_id))->setDec('user_see_card',1);
 					}else{
 						$output = array('code'=>3000,'data'=>array());
 						exit(json_encode($output));
 					}
 				}
 			}else{//如果该用户是会员，则校验是否过期
 				$expire_time = $res['expire_time'];
 				if(time()-$expire_time>0){//会员以过期
 					$output = array('code'=>3000,'data'=>array());
 					exit(json_encode($output));
 				}else{
 					$result = 1;
 				}
 			}
 		}else{//如果是查看自己的私密动态，不需要条件
 			$result = 1;
 		}
 		if($res3&&$res4){
 			$user -> commit();//提交事务
 		}
 		if($result == 0){//如果返回result=0,则无权查看
 			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}
		$res2 = $pri->field('wh_private_space.pri_id,pri_small_img,pri_mid_img,pri_total_zan,pri_zan_people,
			wh_user.user_id,user_nickname,user_logo,user_coin,user_city,pri_content,pri_pub_time')
		->join('wh_pri_pics as pp on pp.pri_id=wh_private_space.pri_id',LEFT)
		->join('wh_user on wh_user.user_id=wh_private_space.pri_id',LEFT)
 			->where(array('pri_user_id'=>$user_id_find))->limit($num1,$d1)->select();//数据库查询数据
 		if($num1==0 && $res2==null){
 			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
 		}elseif($res2){
 			foreach ($res2 as $key => $value) {
 				$zan = explode(',',$value['pri_zan_people']);
				//对标签以及点赞的人进行处理
 				$map = array();
				$map['user_id'] = array('in',$zan);//构造in查询语句
				$user_nickname = $user -> field('user_nickname')->where($map)->select();
				$user_nicknames=array_column($user_nickname,'user_nickname');//二维数组转为一维数组
				$res2[$key]['pri_zan_people_nickname'] = $user_nicknames;
				$p_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_province'])) ->find();
				$c_code = $region -> field('region_code') -> where(array('region_name'=>$value['user_city'])) ->find();
				$res[$key]['province'] = array($value['user_province'],$p_code['region_code']);
				$res[$key]['city'] = array($value['user_city'],$c_code['region_code']);
				unset($res2[$key]['dt_zan_people']);//删除数组键
				unset($res2[$key]['user_provinceer_']);//删除数组键
				unset($res2[$key]['user_city']);//删除数组键
			}
			$output = array('code'=>0,'data'=>array('list'=>$res2));
			exit(json_encode($output));
		}else{
			$output = array('code'=>2000,'data'=>array());
			exit(json_encode($output));
		}
 	}
 	//获取单条私密动态信息
	public function private_one(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
 		//$data = I('get.');
 		$pri_id = $data['pri_id'];
 		$pri_pics = D('Pri_pics');
 		$pri = D('Private_space');
 		$res = $pri -> field('user_nickname,user_coin,user_logo,pri_user_id,pri_pub_time,pri_total_zan,pri_content,
 			pri_zan_people,pri_img')->join('wh_user on wh_user.user_id=wh_private_space.pri_user_id')->
 			join('wh_pri_pics as pic on pic.pri_id=wh_private_space.pri_id','LEFT')
 			->where(array('wh_private_space.pri_id'=>$pri_id))->find();//从数据库查出所需信息
 		//dump($res);
 		if($res == null){
 			$output = array('code'=>2002,'data'=>array());
 			exit(json_encode($output));
 		}elseif($res){
 			$user = D('User');
			$zan = explode(',',$res['pri_zan_people']);//将该字段的字符串数据改为数组
			//dump($tag);
			$map = array();
			$map['user_id'] = array('in',$zan);//构建in条件，user_id in $zan 
			$user_nickname = $user -> field('user_nickname')->where($map)->select();
			$user_nicknames=array_column($user_nickname,'user_nickname');//取出数组中的某一列数据
			$res['pri_zan_people_nickname'] = $user_nicknames;//在结果中添加pri_zan_people_nickname数据
			unset($res['pri_zan_people']);
 			$output = array('code'=>0,'data'=>array('user_nickname'=>$res['user_nickname'],'user_coin'=>$res['user_coin'],
 			'user_logo'=>$res['user_logo'],'pri_user_id'=>$res['pri_user_id'],'pri_pub_time'=>$res['pri_pub_time'],'pri_total_zan'=>$res['pri_total_zan'],
 			'pri_content'=>$res['pri_content'],'pri_zan_people_nickname'=>$res['pri_zan_people_nickname'],'pri_img'=>$res['pri_img']));
 			exit(json_encode($output));			
		}	
 	}
 	//获取用户关注的人信息(多条)
	public function care_peo_info(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		//$data = I('get.');
		$num1 = $data['num1'];
		$d1 = $data['length'];
		$user_id = $data['user_id'];
		$user = D('User');
		$map = array();
		$user_care = $user ->field('user_care')->where(array('user_id'=>$user_id))->find();//查询该用户关注的人
		if($user_care == null){//返回数据为null，该用户不存在
			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
		}elseif($user_care['user_care'] == null){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}
		$care1 =$user_care['user_care'];//从数组中取出用户关注的人
		$care = explode(',',$care1);
		$map['user_id'] = array('in',$care);
		$res = $user -> field('user_id,user_logo,user_sign,user_sex,user_nickname,user_tag,user_zan')->
		where($map)->limit($num1,$d1)->select();
		if($res){
			foreach($res as $key => $value){
				$tag = explode(',',$value['user_tag']);
				$res[$key]['user_tag'] =$tag;
			}
			$output = array('code'=>0,'data'=>array('list'=>$res));
			exit(json_encode($output));
		}else{
			$output = array('code'=>2000,'data'=>array());
			exit(json_encode($output));
		}		
	}
	//获取用户详情信息
	public function user_info(){
		// $json = file_get_contents('php://input');
		// $data = json_decode($json,true);
		$data = I('get.');
		$user_id = $data['user_id'];//当前用户ID
		$user_id_find = $data['user_id_find'];//被查询信息的用户ID
		$user = D('User');
		$region = D("Region");
		$map['user_id'] = array('in',array($user_id,$user_id_find));
 		$res = $user->field('user_id')->where($map)->select();//数据库查询用户是否存在
 		if($user_id!==$user_id_find&&count($res)!==2){//用户查看的是别人的信息，返回res为两条数据】
 			$output = array('code'=>2001,'data'=>array());
			exit(json_encode($output));
 		}
		$res = $user -> field('user_id,user_tel,user_logo,user_big_logo,user_nickname,user_tag,user_city,user_province,user_collect_task_num,
			user_sign,user_prize,user_constell,user_zan,user_coin,user_care_num,user_fans_num,user_level')->
		where(array('user_id'=>$user_id_find))->find();//数据库查询用户信息
		//dump($res);
		$p_code = $region -> field('region_code') -> where(array('region_name'=>$res['user_province'])) ->find();
		$c_code = $region -> field('region_code') -> where(array('region_name'=>$res['user_city'])) -> find();
		$province = array($res['user_province'],$p_code['region_code']);
		$city = array($res['user_city'],$c_code['region_code']);
		$tag = explode(',',$res['user_tag']);
		$res['user_tag'] =$tag;
		if($user_id !== $user_id_find){
			$res1 = $user -> field('user_care') ->where("user_id = $user_id") -> find();
			$user_care = explode(',',$res1['user_care']);
			$a = in_array($user_id_find,$user_care);
			if($a){
				$res['is_care'] = 1;
			}else{
				$res['is_care'] = 0;
			}
		}else{
			$res['is_care'] = 2;
		}
		//dump($res);
		$output = array('code'=>0,'data'=>array('user_id'=>$res['user_id'],'user_logo'=>$res['user_logo'],'user_big_logo'=>$res['user_big_logo'],'user_nickname'=>$res['user_nickname'],
			'user_tag'=>$res['user_tag'],'is_care'=>$res['is_care'],'user_collect_task_num'=>$res['user_collect_task_num'],
			'city'=>$city,'user_tel'=>$res['user_tel'],'province'=>$province,'user_sign'=>$res['user_sign'],'user_prize'=>$res['user_prize'],
			'user_constell'=>$res['user_constell'],'user_zan'=>$res['user_zan'],'user_coin'=>$res['user_coin'],'user_care_num'=>$res['user_care_num'],
			'user_fans_num'=>$res['user_fans_num'],'user_level'=>$res['user_level']));
		exit(json_encode($output));
	}
 	//用户对动态点赞和取消赞
 	public function dt_zan_switch(){
 		$json = file_get_contents('php://input');
 		$data = json_decode($json,true);
 		// $data = I('get.');
		$user_id = $data['user_id'];
		$dt_id = $data['dt_id'];
		$zan = $data['zan'];//获取zan的开关参数，0-》取消，1-》点赞
 		$dt      = D('Dongtai');
 		$user    = D('User');
 		$dt_zan_people = $dt -> field('dt_zan_people,dt_user_id') ->where(array('dt_id'=>$dt_id))->find();//查看这条动态的点赞人
 		$zans = explode(',',$dt_zan_people['dt_zan_people']);
 		if($zan == 0){//实现取消赞功能
 			$key = array_search($user_id,$zans);//查看当前user是否在动态点赞人中
 			if($key === false){
 				$output = array('code'=>2000,'data'=>array());
 				exit(json_encode($output));
 			}elseif($key !== false){//如果当前用户在动态点赞人中，则进行下列步骤
 				$dt -> startTrans();//开启事务
 				unset($zans[$key]);//将当前点赞人的数组中$K对应的数据删除，该数据即为当前用户id，实现取消点赞
 				$zans = implode(',',$zans);//将点赞的人的数组改为字符串，以便存入数据库
 				$data['dt_zan_people'] = $zans;
				$data['dt_total_zan'] = array('exp','dt_total_zan-1');
 				$res2 = $dt ->where(array('dt_id'=>$dt_id))->save($data);
 				$res3 = $user ->where(array('user_id'=>$dt_zan_people['dt_user_id']))->setDec('user_zan',1);//如果取消成功，就将该动态用户点赞总数减一
 				$map['user_id'] = array('in',$zans);
 				$res1 = $user ->field('user_nickname')->where($map)->select();//取出取消赞成功后的所有点赞人昵称
 				$user_nickname=array_column($res1,'user_nickname');
 				//dump($user_nickname);
 				$num = count($res1);//统计取消点赞后剩余的点赞人数
 				if($num == 0){
 					$res1 = 1;//如果取消点赞后该动态点赞人数为0，则需要将res1设为1，使得后面逻辑走通
 				}
 				if($res1 && $res3 &&$res2){
 					$dt -> commit();//提交事务
 					$output = array('code'=>0,'data'=>array('user_nickname'=>$user_nickname));
 					exit(json_encode($output));
 				}else{
 					$dt -> rollback();//执行操作为全完成，回滚事务
 					$output = array('code'=>1,'data'=>array());
 					exit(json_encode($output));
 				}
 			}			
 		}else{//实现点赞功能
 			if(in_array("$user_id",$zans)){//若$user_id已经赞过，则返回code：2
 				$output = array('code'=>2000,'data'=>array());
				exit(json_encode($output));
 			}else{
 				if($dt_zan_people['dt_zan_people'] == null){
 					$a = $user_id;
 				}else{
 					$a = $dt_zan_people['dt_zan_people'].','.$user_id;
 				}
 				$dt -> startTrans();
 				$data['dt_zan_people'] = $a;
				$data['dt_total_zan'] = array('exp','dt_total_zan+1');
 				$res2 = $dt ->where(array('dt_id'=>$dt_id))->save($data);//更新数据库
 				$res3 = $user ->where(array('user_id'=>$dt_zan_people['dt_user_id']))->setInc('user_zan',1);//将该动态用户点赞总数数加一
 				$dt_zan_people = $dt -> field('dt_zan_people,dt_user_id') ->where(array('dt_id'=>$dt_id))->find();//这条动态的点赞人
 				$zans = explode(',',$dt_zan_people['dt_zan_people']);
 				$map['user_id'] = array('in',$zans);
 				$res1 = $user ->field('user_nickname')->where($map)->select();
 				$user_nickname = array_column($res1,'user_nickname');
 				if($res3 && $res2 && $res1){
 					$dt -> commit();
 					$output = array('code'=>0,'data'=>array('user_nickname'=>$user_nickname));
 					exit(json_encode($output));
 				}else{
 					$dt -> rollback();
 					$output = array('code'=>1,'data'=>array());
 					exit(json_encode($output));
 				}
 			}	
 		}
 	}
 	//用户发动态
 	public function dt_pub(){
		$dt = new \Api\Model\DongtaiModel();
		//if(IS_POST){
			// dump($_FILES);
			// exit;
			$data['dt_content'] = $_POST['dt_content'];
			$data['dt_user_id'] = $_POST['dt_user_id'];
			 if($_FILES['dt_img']['error']===0){
			 	$data = $dt -> create($data);
 				$res = 	$dt -> add();
			 }else{
			 	$output = array('code'=>2004,'data'=>array());
				exit(json_encode($output));
			 }
 			
		// }else{
		// 	$this -> display();
		// }		
 	}
 	 //用户发私密动态
 	public function private_pub(){
		$pri = new \Api\Model\PrivateSpaceModel();
		//$pri = D("Private_space");
		// if(IS_POST){
			$data['pri_content'] = $_POST['pri_content'];
			$data['pri_user_id'] = $_POST['pri_user_id'];
			 if($_FILES['dt_img']['error']===0){
			 	$data = $pri -> create($data);
 				$res = $pri->add();
			 }else{
			 	$output = array('code'=>2004,'data'=>array());
				exit(json_encode($output));
			 }
 			
		// }else{
		// 	$this -> display();
		// }		
 	}
 	//用户删除动态
 	public function dt_del(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];//获取动态的发布者ID
		$dt_id = $data['dt_id'];//获取动态ID
		$dt = D("Dongtai");
		$dt_user_id = $dt -> field('dt_user_id')->find($dt_id);//查出该动态的发布者
		$dt_user_id = $dt_user_id['dt_user_id'];
		if($user_id == $dt_user_id){//如果获取的用户ID与读取的用户ID相同，实现删除
			$res = $dt -> where(array('dt_user_id'=>$user_id,'dt_id'=>$dt_id))->limit('1')->delete();
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}else{//不相同返回code2
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}
 	}
 	//用户删除私密动态
 	public function private_del(){
 		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		//$data = I('get.');
		$user_id = $data['user_id'];//获取动态的发布者ID
		$pri_id = $data['pri_id'];//获取动态ID
		$pri = D("Private_space");
		$pri_user_id = $pri -> field('pri_user_id')->find($pri_id);//查出该动态的发布者
		$pri_user_id = $pri_user_id['pri_user_id'];
		if($user_id == $pri_user_id){//如果获取的用户ID与读取的用户ID相同，实现删除
			$res = $pri -> where(array('pri_user_id'=>$user_id,'pri_id'=>$pri_id))->limit('1')->delete();
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output));
		}else{//不相同返回code2
			$output = array('code'=>3000,'data'=>array());
			exit(json_encode($output));
		}
 	}
}
