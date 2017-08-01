<?php
namespace Api\Controller;
use 	  Think\Controller;
class ApiAreaController extends Controller{
	public function get_area(){
		if(S('area')){
			$output = array('code'=>0,'data'=>array('province'=>S('area')));
			exit(json_encode($output));
		}else{
			$region = D('Region');
			$arr = $region -> select();
			$res = $this -> tree($arr,1);
		//dump($res);
			$arr = array();
			$i = 0;
			foreach ($res as $key => $value) {
				$arr[$i]['p_name'] = $key;
				$arr[$i]['p_code'] = $value['code'];
				$arr[$i]['city'] = $value['area'];
				$i = $i+1;
			}
			dump($arr);
			S('area',$arr);
			if($res){
				$output = array('code'=>0,'data'=>array('province'=>$res));
				exit(json_encode($output));
			}else{
				$output = array('code'=>3000,'data'=>array());
				exit(json_encode($output));
			}
		}
		
	}
	public function test(){
		$region = D('Region');
		$arr = $region -> select();
		$res = $this -> tree($arr,1);
		//dump($res['北京市']);
		$arr = array();
		$i = 0;
		foreach ($res as $key => $value) {
			$arr[$i]['p_name'] = $key;
			$arr[$i]['p_code'] = $value['code'];
			$arr[$i]['city'] = $value['area'];
			$i = $i+1;
		}
		dump($arr);
	}
	static function tree($arr,$parent_id){
		static $res = array();
		foreach ($arr as $v) {
			if($v['parent_id'] == $parent_id){
				$i = 0;
				foreach ($arr as $v1) {
					if($v1['parent_id'] == $v['region_id']){
						$v[$v['region_name']]['code'] = $v['region_code'];
						$v[$v['region_name']]['area'][$i]['c_name'] = $v1['region_name'];
						$v[$v['region_name']]['area'][$i]['c_code'] = $v1['region_code'];
						$i = $i+1;
						self::tree($arr,$v1['region_id']);
						$res[$v['region_name']] = $v[$v['region_name']];
					}
				}
			}
		}
		return $res;
	}
}