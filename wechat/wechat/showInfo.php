<meta charset="utf-8">
<?php
//查看用户报名信息
$mem = new Memcache();
$mem->connect('127.0.0.1',11211);
// echo '<pre>';
// var_dump($mem->getStats());
$stats = $mem->getStats();
echo '现在已经有'.$stats['curr_items'].'人报名了！';