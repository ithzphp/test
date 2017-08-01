<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="renderer" content="webkit">
<title></title>
<link rel="stylesheet" href="<?php echo (C("BACK_CSS_URL")); ?>/pintuer.css">
<link rel="stylesheet" href="<?php echo (C("BACK_CSS_URL")); ?>/admin.css">
<script src="<?php echo (C("BACK_JS_URL")); ?>/jquery.js"></script>
<script src="<?php echo (C("BACK_JS_URL")); ?>/pintuer.js"></script>
</head>
<body>
<div class="panel admin-panel">
  <div class="panel-head"><strong class="icon-reorder"> 内容列表</strong></div>
  <div class="padding border-bottom">  
  <!-- <button type="button" class="button border-yellow"><span class="icon-plus-square-o"></span> --> <a href="<?php echo U('News/add');?>">添加新闻</a><!-- </button> -->
  </div>
  <table class="table table-hover text-center">
    <tr>
      <th width="10%">ID</th>
      <th width="20%">图片</th>
      <th width="15%">新闻标题</th>
      <th width="20%">新闻内容</th>
      <th width="10%">排序</th>
      <th width="15%">操作</th>
    </tr>
   
    <tr>
      <td>1</td>     
      <td><img src="<?php echo (C("BACK_IMG_URL")); ?>/11.jpg" alt="" width="120" height="50" /></td>     
      <td>首页焦点图</td>
      <td>描述文字....</td>
      <td>1</td>
      <td><div class="button-group">
      <a class="button border-main" href="<?php echo U('News/update');?>"><span class="icon-edit"></span> 修改</a>
      <a class="button border-red" href="javascript:void(0)" onclick="return del(1,1)"><span class="icon-trash-o"></span> 删除</a>
      </div></td>
    </tr>
 
    
  </table>
</div>
<script type="text/javascript">
function del(id,mid){
	if(confirm("您确定要删除吗?")){
	
	}
}
</script>
</body></html>