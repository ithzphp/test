<?php

namespace Api\Model;
use Think\Model;

class NewsModel extends Model{
	 protected function _after_insert($data,$options) {
	 	dump($data);
	 	$cfg = array(
				'rootPath' => './Common/Uploads/news_img/',
			);//给出存放根目录
			$up = new \Think\Upload($cfg);//new 上传类
			dump($_FILES);
			$z = $up -> uploadOne($_FILES['news_img']);
			dump($z);
			if($z){//如果文件上传成功，进行存储设置
				$user_big_logo = $up ->rootPath.$z['savepath'].$z['savename'];//文件初始存储地址
				$site = 'www.wh.com';//定义服务器地址
				//$site = 'http://120.92.35.211/wanghong/wh';
				// $user_big_logo1 = $site.substr($user_big_logo,1);//拼接加上服务器的URL，存入数据库			
				// $data['user_big_logo'] = $user_big_logo1;
				//制作缩略图
				$im = new \Think\Image();
          		$im -> open($user_big_logo); //打开原图
           		$im -> thumb(240,240); //制作缩略图
            	//缩略图名字：“small_原图名字”
          		$news_img = $up->rootPath.$z['savepath']."small_".$z['savename'];//给出文件的地址
            	$im -> save($news_img);//存储缩略图到服务器
            	$news_img1 = $site.substr($news_img,1);//拼接加上服务器的URL，存入数据库
				$data['news_img'] = $news_img1;
				dump($data);
				$user = D("User");
				$news_id = $data['news_id'];
				dump($news_id);
				$res = $this ->where("news_id = $news_id")->setField('news_img',$news_img1);
				dump($res);
				if($res || $res==0){
					$output = array('code'=>'0','data'=>(object)array());
					exit(json_encode($output));
				}
		}else{
			$output = array('code'=>'2004','data'=>array());
			exit(json_encode($output));
		}
	}
}