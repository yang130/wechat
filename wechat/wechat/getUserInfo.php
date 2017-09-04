<?php
//引入类文件
require './wechat.class.php';
header('Content-Type:text/html;charset=utf-8');
//接收用户信息，进行数据写入操作
  //网页授权之后的回调页面
  //可以获取到传输的code
  //通过code换取accesstoken和openid
  //accesstoken和openid可以获取用户基本信息
  $code = $_GET['code'];
  //1.url
  $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.APPID.'&secret='.APPSECRET.'&code='.$code.'&grant_type=authorization_code ';
  //2.请求方式
  //3.发送请求
  $wechat = new Wechat();
  $content = $wechat->request($url);
  //4.处理返回值
  $content = json_decode($content);
  //获取网页授权access_token和openID
  $access_token = $content->access_token;
  $openid = $content->openid;
  // echo $access_token.'<br />'.$openid;
  //获取用户信息
  //1.url
  $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
  //2.请求方式
  //3.发送请求
  $content = $wechat->request($url);
  //4.处理返回值
  $content = json_decode($content);
  // var_dump($content);
  // 处理性别信息
  switch ($content->sex) {
    case '1':
      $sex = '男';
      break;
    case '2':
      $sex = '女';
      break;
    default:
      $sex = '未知';
      break;
  }
?>
<!DOCTYPE html>
<html>
<head>
  <title>报名活动</title>
</head>
<body>
  <form action='./addInfo.php' method="post">
    昵称:<input type="text" name="nickname" value="<?php echo $content->nickname;?>"><br />
    性别:<input type="text" name="sex" value="<?php echo $sex;?>"><br />
    手机号码:<input type="text" name="telphone" value=""><br />
    <input type="hidden" name="openid" value="<?php echo $openid;?>"><br />
    <input type="submit" name="" value="报名">
  </form>
</body>
</html>

