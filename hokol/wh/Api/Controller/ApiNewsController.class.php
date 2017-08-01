<?php
namespace Api\Controller;
use Think\Controller;
class ApiNewsController extends Controller{
	//新闻主页接口
	public function news(){
		//多条新闻数据接口
		$output = array();
		$json = file_get_contents('php://input');
		$data = json_decode($json,true);
		// $data = I('get.');
		$num1 = $data['num1'];
		$d1 = $data['length'];//得出要取的数据量
		$news = D('News'); 
		$a = $news -> count('news_id');//查看数据库新闻数量
		//dump($a);
		if($a == 0){
			$output = array('code'=>0,'data'=>(object)array());
			exit(json_encode($output)); 
		}else{
			$res = $news -> field('news_id,news_time,news_title,news_img,news_source')
			->order('news_time desc')->limit("$num1,$d1")->select();
			if($res){
				foreach ($res as $key => $value) {
					$news_id = $value['news_id'];
					$res[$key]['url'] = "http://www.wh.com/index.php/Api/ApiNews/new_1?news_id=$news_id";
					//$res[$key]['url'] = "http://120.92.35.211/wanghong/wh/index.php/Api/ApiNews/new_1?news_id=$news_id";

				}
				//dump($res);
				$output = array('code'=>0,'data'=>array('list'=>$res));
				exit(json_encode($output));
			}else{
				$output = array('code'=>2000,'data'=>array());
				exit(json_encode($output));	
			} 
		}
	}
	//查询单条新闻数据
	public function find_data($news_id){
		$news = D('News');
		//$data1 = array();
		$newsinfo  =$news->where(array('news_id'=>$news_id))->
		field('news_id,news_time,news_content,news_title,news_img,news_source')
		->find();//数据库中查询数据
		return $newsinfo;
	}
	//推荐新闻数据接口
	// public function new_tui1(){
	// 	$output = array();
	// 	$news = D('News');
	// 	$res  =$news->field('news_id,news_time,news_content,news_title,news_img,news_source')
	// 	->where(array('is_tui'=>'1'))->order('news_time desc')->find();
	// 	$news_id = $res['news_id'];
	// 	if($res){
	// 		$url = "http://www.wh.com/index.php/Api/ApiNews/new_1?news_id=$news_id";
	// 		$output = array('code'=>0,'data'=>array('news_id'=>$res['news_id'],'news_time'=>$res['news_time'],
	// 			'news_title'=>$res['news_title'],'news_img'=>$res['news_img'],
	// 			'news_source'=>$res['news_source'],'url'=>$url));
	// 		exit(json_encode($output));
	// 	}else{//没有推荐新闻数据
	// 		$output = array('code'=>0,'data'=>(object)array());
	// 		exit(json_encode($output));
	// 	}
	// }
	//显示新闻
	public function new_1(){
		$data = I('get.');
		$news_id = $data['news_id'];
		$data = $this->find_data($news_id);
		$this -> assign('newsinfo',$data); 
		$this->display();
	}
	//添加新闻推荐
	public function add_news_rec(){
		
	}
	//新闻推荐
	public function new_tui(){
		$news_rec = D("News_recommend");
		$res = $news_rec ->field('banner_img,rec_info,type')-> limit(1)->find();
		$output = array('code'=>0,'data'=>array('banner_img'=>$res['banner_img'],'info'=>$res['rec_info'],'type'=>$res['type']));
		exit(json_encode($output));
	}
}