<meta charset="utf-8">
<?php
// header('Content-type:text/html;charset=uft-8');
require './wechat.class.php';
$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.APPID.'&secret='.APPSECRET.'&code='.$_GET['code'].'&grant_type=authorization_code';
$wechat = new Wechat();
$content = $wechat->request($url);
// var_dump($content);die;
$content = json_decode($content);
$url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$content->access_token.'&openid='.$content->openid.'&lang=zh_CN';
$content = $wechat->request($url);
var_dump($content);die;
