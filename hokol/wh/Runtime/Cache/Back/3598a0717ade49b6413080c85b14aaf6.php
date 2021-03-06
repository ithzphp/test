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
<script type="text/javascript" charset="utf-8" src="<?php echo (C("PLUGIN_URL")); ?>ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo (C("PLUGIN_URL")); ?>ueditor/ueditor.all.min.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo (C("PLUGIN_URL")); ?>ueditor/lang/zh-cn/zh-cn.js"></script>
</head>
<body>
<div class="panel admin-panel">
  <div class="panel-head" id="add"><strong><span class="icon-pencil-square-o"></span>增加内容</strong></div>
  <div class="body-content">
    <form method="post" class="form-x" action="/index.php/Back/News/add" enctype="multipart/form-data">  
      <div class="form-group">
        <div class="label">
          <label>新闻标题：</label>
        </div>
        <div class="field">
          <input type="text" class="input w50" value="" name="news_title" data-validate="required:请输入标题" />
          <div class="tips"></div>
        </div>
      </div>
      <div class="form-group">
        <div class="label">
          <label>新闻图片：</label>         
        </div>
        <div class="field">
           动态图片: <input type="file" name="news_img"></br>
        </div>
      </div>    
      <div class="form-group"> 
          <div class="label">
            <label>该新闻是否推荐：</label>
          </div>
          <div class="field" style="padding-top:8px;"> 
            不推荐 <input name='is_tui' id="ishome"  type="radio" value='0'/>
            推荐 <input name='is_tui' id="isvouch"  type="radio" value='1'/>
          </div>
        </div>
      <div class="form-group">
        <div class="label">
          <label>新闻内容：</label>
        </div>
        <div class="field">
          <textarea name="news_content" id='news_content' class="input" style="width:500px;border:1px solid #ddd;"></textarea>
          <script type='text/javascript'>
              var ue = UE.getEditor('news_content');
         </script>
        </div>
        
      </div>
     
      <div class="clear"></div>
       <div class="form-group">
        <div class="label">
          <label>新闻来源：</label>
        </div>
        <div class="field">
          <input type="text" class="input w50" name="news_source" value=""  />
          <div class="tips"></div>
        </div>
      </div>
      <div class="form-group">
        <div class="label">
          <label>新闻作者：</label>
        </div>
        <div class="field">
          <input type="text" class="input w50" name="authour" value=""  />
          <div class="tips"></div>
        </div>
      </div>
      <div class="form-group">
        <div class="label">
          <label></label>
        </div>
        <div class="field">
          <button class="button bg-main icon-check-square-o" type="submit"> 提交</button>
        </div>
      </div>
    </form>
  </div>
</div>

</body></html>