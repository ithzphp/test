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
<form method="post" action="/index.php/Back/Data/login1">
    用户手机：<input type="text" name="user_tel"></br>
    用户密码<input type="text" name="user_pwd"></br>
    token<input type="text" name="token"></br>
    <input type="submit" value="提交">
</form>



</body>