<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
    <script src="<?php echo (C("BACK_JS_URL")); ?>/jquery.js"></script>
    <script src="<?php echo (C("BACK_JS_URL")); ?>/pintuer.js"></script>
    <script src="<?php echo (C("BACK_JS_URL")); ?>/jquery.form.js"></script>
</head>
<body>
<input type="file" id="fileupload" name="mypic"/>
<img src="" alt="" id="mypic"/>
<script>
    $(function(){
        var files=$(".files");
        $('#fileupload').wrap('<form id="myupload" action="/index.php/Back/Data/test" method="post" enctype="multipart/form-data"></form>')
    })
    $("#fileupload").change(function(){$('#myupload').ajaxSubmit({
        success:function(data){
            $("#pic").attr("src","files/"+data);
        },
        error:function(xhr){
            alert("上传失败");
        }
    })})
</script>
</body>
</html>