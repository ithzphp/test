<?php
namespace Back\Controller;
use Think\Controller;

class NewsController extends Controller{
	public function add(){
		$news = new \Api\Model\NewsModel();
		if(IS_POST){
		//$news = D('News');
 		$rules = array(array('news_time','time','1','function'));//构造自动添加发布时间语句
 		$data = $news -> auto($rules)->create($data);//实现自动添加时间
 		dump($data);
 		$data['news_content'] = \fanXSS($_POST['news_content']);
 		$res = $news->add($data);
		}else{
			$this->display();
		}
	}
	public function update(){
		$this->display();
	}
	public function delete(){
		$this ->display();	
	}
	public function add1(){
		$news = new \Api\Model\NewsModel();
		//$data = I("get.");
		//http://120.92.35.211/wanghong/wh/index.php/Back/News/add
		$arr['news_content'] = \fanXSS($_GET['news_content']);
		$arr['is_tui'] = $_GET['is_tui'];
		$arr['news_title'] = $_GET['news_title'];
		$arr['news_source'] = $_GET['news_source'];
		$rules = array(array('news_time','time','1','function'));//构造自动添加发布时间语句
		$arr = $news -> auto($rules)->create($arr);//实现自动添加时间
		$res = $news ->add();
		if($_FILES['dt_img']['error']===0){
			$rules = array(array('news_time','time','1','function'));//构造自动添加发布时间语句
			$arr = $news -> auto($rules)->create($arr);//实现自动添加时间
			$res = $news ->add();
		}else{
			$output = array('code'=>2004,'data'=>array());
			exit(json_encode($output));
		}
	}
	public function get_data(){
		$data = I("get.");
		$num = $data['num'];
	}
}