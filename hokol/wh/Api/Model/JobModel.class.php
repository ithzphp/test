<?php

namespace Api\Model;
use Think\Model;

class JobModel extends Model{
	protected function _before_update($data,$options){
		$res = $this -> where('job_id = 8') ->delete();
		if($res){
			$options['status'] = 1;
		}else{
			$options['status'] = 0;
		}
	}
		
		
}