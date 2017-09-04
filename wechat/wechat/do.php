<?php
header('Content-Type:text/html;charset=utf-8');
//类测试方法文件
//引入类文件
require './wechat.class.php';
//实例化
$wechat = new Wechat();
//调用方法
// $wechat->getAccessToken();
// $wechat->getTicket();
// $wechat->getQRCode();
// $wechat->getUserList();
// $wechat->getUserInfo();
// $wechat->uploadFile();
// $wechat->getFile();
$wechat->createMenu();
// $wechat->showMenu();
// $wechat->delMenu();
// $wechat->send();
// $wechat->amapLBS();
// $wechat->sendAll();