<?php

namespace Api\Model;
use Think\Model;

class DongtaiModel extends Model{
	 protected $_auto = array(
        array('dt_pub_time','time',1,'function'),
    );
	 //  protected function _before_insert(&$data,$options) {
	 //  		dump($data);
	 //  }
	    // 插入成功后的回调方法
    protected function _after_insert($data,$options) {
    		//dump($data);
    		//dump($_FILES);
           $cfg = array(
              'rootPath' => './Common/Uploads/dt_img/'
            );//给出存放根目录
           $up = new \Think\Upload($cfg);//new 上传类
           $z = $up -> uploadOne($_FILES['dt_img']);
           if($z){//如果文件上传成功，进行存储设置
               $dt_img = $up ->rootPath.$z['savepath'].$z['savename'];//文件初始存储地址
                //dump($dt_img);
               $site = 'www.wh.com';//定义服务器地址
                //$site = 'http://120.92.35.211/wanghong/wh';
                 //对文件内容进行检测
               $finfo = finfo_open(FILEINFO_MIME_TYPE);
               $type = finfo_file($finfo,$dt_img);//返回文件类型信息
                   //$b = substr($dt_img,0,strrpos($dt_img,'.'));//取出文件地址后缀名之前的内容
                   // $a = strtr(strrchr($type,'/'),'/','.');//取出文件真实的后缀名
                   // $c = $b.$a;//拼装更改后缀名后的新地址
                    // $d = rename($dt_img,$c);//对旧地址重新命名
               $dt_img1 = $site.substr($dt_img,1);//拼接加上服务器的URL，存入数据库  
               $e = substr($type,0,(strpos($type,'/')));//获取文件类型，进行判断
               finfo_close($finfo);
              if($e == 'image'){//如果源文件为图片，则制作缩略图
                   //2) 根据原图($big_path_name)制作动态中图
                   $im = new \Think\Image();
                   $im -> open($dt_img); //打开原图
                   $im -> thumb(80,80); //制作缩略图
                   //缩略图名字：“small_原图名字”
                   $dt_small_img = $up->rootPath.$z['savepath']."small_".$z['savename'];//给出文件的地址
                   $im -> save($dt_small_img);//存储缩略图到服务器
                   $dt_small_img1 = $site.substr($dt_small_img,1);//拼接加上服务器的URL，存入数据库
                 }
                //dump($dt_small_img1);
              $arr = array(
                'dt_img' => $dt_img1,
                'dt_small_img'=>$dt_small_img1,
                'dt_id' => $data['dt_id']
                );
              $res1 = D('Dt_pics') -> add($arr);
              if($res1){
                $output = array('code'=>0,'data'=>array());
                exit(json_encode($output));
              }	
         }		
    }
}