<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="renderer" content="webkit">
<title>任务发布</title>
<script src="<?php echo (C("BACK_JS_URL")); ?>/jquery.js"></script>
<script src="<?php echo (C("BACK_JS_URL")); ?>/pintuer.js"></script>
</head>
<body>
<form method="post" action="/index.php/Back/Ios/task_pub">
	任务标题：<input type="text" name="task_title"></br>
	任务内容：<input type="text" name="task_content"></br>
	结束时间：<input type="text" name="task_end_time"></br>
	用户id:	 <input type="text" name="task_user_id"></br>
	任务费用：<input type="text" name="task_fee"></br>
	任务类型：<input type="text" name="task_type"></br>
			<input type="submit" value="提交">

</form>



</body>