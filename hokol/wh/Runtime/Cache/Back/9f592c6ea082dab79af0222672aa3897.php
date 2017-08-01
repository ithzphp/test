<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>登录</title>  
    <link rel="stylesheet" href="<?php echo (C("BACK_CSS_URL")); ?>/pintuer.css">
    <link rel="stylesheet" href="<?php echo (C("BACK_CSS_URL")); ?>/admin.css">
    <script src="<?php echo (C("BACK_JS_URL")); ?>/jquery.js"></script>
    <script src="<?php echo (C("BACK_JS_URL")); ?>/pintuer.js"></script>  
    <script type="text/javascript" src="<?php echo (C("COMMON_URL")); ?>/Js/jquery-1.11.3.min.js"></script>
</head>
<body>
<div class="bg"></div>
<div class="container">
    <div class="line bouncein">
        <div class="xs6 xm4 xs3-move xm4-move">
            <div style="height:150px;"></div>
            <div class="media media-y margin-big-bottom">           
            </div>         
            <form action="/index.php/Back/Admin/login" method="post">
            <div class="panel loginbox">
                <div class="text-center margin-big padding-big-top"><h1>后台管理中心</h1></div>
                <div class="panel-body" style="padding:30px; padding-bottom:10px; padding-top:10px;">
                    <div class="form-group">
                        <div class="field field-icon-right">
                            <input type="text" class="input input-big" name="admin_name"  placeholder="登录账号" data-validate="required:请填写账号" />
                            <span class="icon icon-user margin-small"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="field field-icon-right">
                            <input type="password" class="input input-big" name="admin_pwd" placeholder="登录密码" data-validate="required:请填写密码" />
                            <span class="icon icon-key margin-small"></span>
                        </div>
                    </div>

  
                    <div class="form-group">

     <script type="text/javascript">//通过ajax校验验证码是否正确
        var code_flag = false; 
        function check_code(){
            var code = $('#captcha').val();
            if(code.length==4){
                $.ajax({
                    url:"<?php echo U(checkCode);?>",
                    data:{'code':code},
                    dataType:'json',
                    type:'get',
                    success:function(msg){
                        if(msg.status==1){
                        $('#code_check_res').html('<span style="color:green">验证码正确</span>');
                        code_flag = true;
                    }else{
                        $('#code_check_res').html('<span style="color:red">验证码错误</span>');
                        code_flag = false;
                        }
                    }
                });
            }
        }
    </script>
                        <div class="field">
                            <input type="text" id='captcha' class="input input-big" name="code" maxlength='4' onkeyup="check_code()" />
                           <img src="<?php echo U('verifyImg');?>" alt="验证码" width="100" height="32" class="passcode" style="height:43px;cursor:pointer;" onclick="this.src=this.src+'?'">  
                            </div>
                            <div>
                            <ul>
                                <li class="user_main_input" id="code_check_res">
                                </li>
                            </ul>                        
                        </div>
                    </div>
                </div>
                <div style="padding:30px;"><input type="submit" class="button button-block bg-main text-big input-big" value="登录"></div>
            </div>
            </form>  
                  
        </div>
    </div>
</div>

</body>
</html>