<?php
header('Content-Type:text/html;charset=utf-8');
$nickname = $_POST['nickname'];
$sex = $_POST['sex'];
switch ($sex) {
  case '男':
    $sexNum = 1;
    break;
  case '男':
    $sexNum = 2;
    break;
  default:
    $sexNum = 0;
    break;
}
$telphone = $_POST['telphone'];
//组合数据
$data = array(
    'nickname' => $nickname,
    'sex' => $sexNum,
    'telphone' => $telphone,
    'addTime' => time(),  //添加时间
  );
//往memcache存储
$mem = new Memcache();
$mem->connect('127.0.0.1',11211);
$rs = $mem->set($_POST['openid'],$data);
if($rs){
  echo '报名成功！请等候客服联系!';
}else{
  echo '报名失败，请联系客服!';
}