<?php
namespace Job\Controller;
use Think\Controller;
//use Think\Controller;

class JobController extends Controller{
	//后台登录部分
	public function job_info(){
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		//$data = I('get.');
		$job_id = $data['job_id'];
		$job = D('Job');
		$res = $job -> find($jod_id);
		$job_require = explode('/',$res['job_require']);
		$res['job_require'] = $job_require;
		//dump($res);
		if($res){
			$output = array('code'=>'0','data'=>array('jod_id'=>$res['job_id'],
			'job_name'=>$res['job_name'],'job_require'=>$res['job_require']));
			exit(json_encode($output));
		}else{
			$output = array('code'=>'1','data'=>array());
			exit(json_encode($output));
		}
		
	}
}