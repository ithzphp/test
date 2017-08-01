<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="renderer" content="webkit">
<title>dongtai发布</title>
<script src="<?php echo (C("BACK_JS_URL")); ?>/jquery.js"></script>
<script src="<?php echo (C("BACK_JS_URL")); ?>/pintuer.js"></script>
</head>
<body>
<form method="post" action="/index.php/Api/ApiDongtai/dt_pub" enctype="multipart/form-data">
	动态内容：<input type="text" name="dt_content"></br>
	用户id:	 <input type="text" name="dt_user_id"></br>
	是否私密: <input type="text" name="is_private"></br>
	动态图片: <input type="file" name="dt_img"></br>
	<input type="submit" value="提交">
</form>

</body>