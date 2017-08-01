<?php

//后台父类控制器
namespace Common\Tools;
use Think\Controller;

class BaseController extends Controller{
    //构造方法
    function __construct(){
        parent::__construct();//先执行父类的，否则父类构造方法被覆盖
        $admin_name = session('admin_name');
        // if($_SERVER['PHP_SELF']=='/index.php/Back/Admin/login.html'){
        //         $this ->error('未登录，请先登录',U('Admin/admin'),1);
        // }
        //  if(empty($admin_name)){
        //         $this ->error('请先登录',U('Admin/login'),1);
        // }
    }
}
