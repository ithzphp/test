<?php
namespace Back\Controller;
use Think\Controller;
//use Think\Controller;

class AdminController extends Controller{
	//后台登录部分
	public function login(){
		if(IS_POST){
		$name = $_POST['admin_name'];
		$pwd = $_POST['admin_pwd'];
		dump($name);
		$manager = D('Admin_user');
		$info = $manager -> where(array('admin_name'=>$name,'admin_pwd'=>$pwd))->find();
		dump($info);
		if($info != null){
			session('admin_id',$info['admin_id']);
			session('admin_name',$info['admin_name']);
			$this->redirect('Index/index');
		}else{
			$this ->error('用户名或密码错误',U('login'),20);
			//$this->redirect('Admin/login',20,'用户名或密码错误');
		}	
		}else{
			$this->display();	
		}
	}
	public function logout(){
		session(null);
		$this->redirect('login');
	}
	//实现验证码
	public function verifyImg(){
		$config = array(
			'fontSize' => 15,
			'length'   => 4,
			'imageH'   =>30,
			'imageW'   =>135,
			'useNoise' => true,
			'fontttf'  =>'4.ttf',
			
			);
		$Verify = new \Think\Verify($config);
		$Verify->entry();	
	}
	//校验验证码模块
	public function checkCode(){
		$code = I('get.code');
		//dump($code);
		$vry = new \Think\Verify();
		if($vry->check($code)){
			echo json_encode(array('status'=>1));
		}else{
			echo json_encode(array('status'=>2));
		}
	}
}