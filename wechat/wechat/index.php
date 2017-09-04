<?php
//项目入口文件
require './wechat.class.php';
//实列化
$wechat = new Wechat();
//验证方法
//如果传输了echostr字符串
//就是来验证的
if($_GET['echostr']){
  $wechat->valid();
}else{
  //调用消息管理方法
  $wechat->responseMsg();
}
