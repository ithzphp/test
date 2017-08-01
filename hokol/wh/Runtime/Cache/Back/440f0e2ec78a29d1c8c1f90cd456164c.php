<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>测试</title>  
</head>
<body>
<form action='/index.php/Back/Index/login.html' method="post">
	用户名：
	<input type='text' name="user_tel"/>
	用户密码：
	<input type='text' name="user_pwd"/>
	token:
	<input type='text' name="token"/>
	<input type="submit">

</form>



</body>
</html>