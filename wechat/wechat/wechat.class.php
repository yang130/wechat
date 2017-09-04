<?php
//引入配置文件
require './wechat.cfg.php';
//定义一个wechat
//所有关于wechat方法
class Wechat{
  //封装
  // private  私有  本类内调用
  // public   公共
  // protected  受保护  继承类可以调用
  private $token;
  //构造方法  初始化参数
  public function __construct()
  {
    //定义属性
    $this->token = TOKEN;
    $this->appid = APPID;
    $this->appsecret = APPSECRET;
    $this->textTpl = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>
          <Content><![CDATA[%s]]></Content>
          <FuncFlag>0</FuncFlag>
          </xml>";

    $this->newsTpl = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[news]]></MsgType>
          <ArticleCount>%s</ArticleCount>
          <Articles>%s
          </Articles>
          </xml>";
    $this->itemTpl = "<item>
          <Title><![CDATA[%s]]></Title>
          <Description><![CDATA[%s]]></Description>
          <PicUrl><![CDATA[%s]]></PicUrl>
          <Url><![CDATA[%s]]></Url>
          </item>";
    $this->imageTpl = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[image]]></MsgType>
          <Image>
          <MediaId><![CDATA[%s]]></MediaId>
          </Image>
          </xml>";
    $this->musicTpl = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[music]]></MsgType>
          <Music>
          <Title><![CDATA[%s]]></Title>
          <Description><![CDATA[%s]]></Description>
          <MusicUrl><![CDATA[%s]]></MusicUrl>
          <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
          <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
          </Music>
          </xml>";
  }
  //校验方法
  public function valid()
  {
          $echoStr = $_GET["echostr"];
          //valid signature , option
          if ($this->checkSignature()) {
              echo $echoStr;
              exit;
          }
  }
  //消息管理
  public function responseMsg()
  {
          //get post data, May be due to the different environments
          $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
          file_put_contents('data.xml', $postStr);
          //extract post data
          if (!empty($postStr)) {
              /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                the best way is to check the validity of xml by yourself */
              libxml_disable_entity_loader(true);
              $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
              //对于不同的消息类型
              //进行不同方法的处理
              switch ($postObj->MsgType) {
                case 'text':
                  $this->doText($postObj);
                  break;
                case 'image':
                  $this->doImage($postObj);
                  break;
                case 'voice':
                  $this->doVoice($postObj);
                  break;
                case 'location':
                  $this->doLocation($postObj);
                  break;
                case 'event':
                  $this->doEvent($postObj);
                  break;
                default:
                  break;
              }
            }
  }
  //检查签名
  private function checkSignature()
  {
          // you must define TOKEN by yourself
          if (!defined("TOKEN")) {
              throw new Exception('TOKEN is not defined!');
          }
          $signature = $_GET["signature"];
          $timestamp = $_GET["timestamp"];
          $nonce = $_GET["nonce"];
          $token = $this->token;
          $tmpArr = array($token, $timestamp, $nonce);
          // use SORT_STRING rule
          sort($tmpArr, SORT_STRING);
          $tmpStr = implode($tmpArr);
          $tmpStr = sha1($tmpStr);
          if ($tmpStr == $signature) {
              return true;
          } else {
              return false;
          }
  }
  //文本消息处理方法
  private function doText($postObj,$keyword)
  {
    //如果传输过来可keyword,就是语音识别结果
    //否则就是xml模本的信息
        isset($keyword) ? $keyword : $keyword = trim($postObj->Content);
        //xml模板
        if (!empty($keyword)) {
          //判断用户接收到图片关键字
          switch ($keyword) {
            case '图片':
              $MediaId = 'JK31gej5wLdEQjNmFHxoPORBMSj3or0_vXdTy-BK8xhaB3b3XI8ztrV-mLQLaTcR';
              //发送一个图片信息回去
              $resultStr = sprintf($this->imageTpl,$postObj->FromUserName,$postObj->ToUserName,time(),$MediaId);
              echo $resultStr;
              break;
            case '歌曲':
              $this->sendMusic($postObj);
              break;
            default:
              # code...
              break;
            exit();
          }
            // $contentStr = "Welcome to wechat world!";
            $contentStr = "你好!我是php59的微信公众号";
            //请求机器人接口
            $url = 'http://api.qingyunke.com/api.php?key=free&appid=0&msg='.$keyword;
            $contentStr = str_replace("{br}","\r",json_decode($this->request($url,false))->content);
            //sprintf 拼接模板
            $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
            // file_put_contents('./data1.xml',$resultStr);
            echo $resultStr;
        }
  }
  //图片消息处理方法
  private function doImage($postObj)
  {
    //拿到发送图片用户上传时的media_id
    $MediaId = $postObj->MediaId;
    $resultStr = sprintf($this->imageTpl,$postObj->FromUserName,$postObj->ToUserName,time(),$MediaId);
    file_put_contents('return.xml',$resultStr);
    echo $resultStr;
    //拼接返回数据模板
    //返回接收到图片url地址
    // $resultStr = sprintf($this->textTpl,$postObj->FromUserName, $this->ToUserName,time(),'text',$postObj->PicUrl);
    // // file_put_contents('./data1.xml',$resultStr);
    // echo $resultStr;
  }
  //语音消息处理方法
  private function doVoice($postObj)
  {
    //获取语音识别结果
    $keyword = $postObj->Recognition;
    //调用文本回复方法
    $this->doText($postObj,$keyword);
    // $MediaID = $postObj->MediaId;
    // $resultStr = sprintf($this->textTpl,$postObj->FromUserName, $this->ToUserName,time(),'text','语音接收到,MediaID:'.$MediaID);
    // echo $resultStr;
  }
  //地理位置消息处理方法
  private function doLocation($postObj)
  {
    $location = $postObj->Location_X.','.$postObj->Location_Y;
    $contentStr = $this->amapLBS($location);
    // $contentStr = '您所在位置在:纬度为:'.$postObj->Location_X.' 经度为:'.$postObj->Location_Y;
    $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $this->ToUserName, time(), 'text', $contentStr);
    echo $resultStr;
  }
  //事件消息的处理方法
  private function doEvent($postObj)
  {
    //事件具有多种类型，不同的事件
    //使用不同的方法处理
    switch ($postObj->Event) {
      case 'subscribe':
        $this->doSubscribe($postObj);  //关注事件处理方法  扫二维码关注事件
        break;
      case 'unsubscribe':
        $this->doUnsubscribe($postObj);  //关注事件处理方法
        break;
      case 'subscribe':
        $this->doSubscribe($postObj);  //关注事件处理方法
        break;
      case 'SCAN':
        $this->doScan($postObj);  //已关注扫描二维码事件
        break;
      case 'CLICK':
        $this->doClick($postObj); //自定义菜单点击事件
        break;
      default:
        # code...
        break;
    }
  }
  //未关注，扫描二维码事件
  private function doSubscribe($postObj)
  {
    //如果存在EventKey
    if(!isset($postObj->EventKey)){
      //判断是否是通过二维码扫描
      $content = '您参加的活动代号为:'.$postObj->EventKey;
      //以文本形式回复
    }else{
      //普通关注，回复欢迎信息
      $content = '感谢关注我们，请经常联系！';
    }
    $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $this->ToUserName, time(), 'text', $content);
    echo $resultStr;
  }
  //已关注，扫描二维码事件
  private function doScan($postObj)
  {
    //判断是否是通过二维码扫描
    $scene_id = '您触发的已关注扫描二维码事件,您参加的活动代号为:'.$postObj->EventKey;
    //以文本形式回复
    $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $this->ToUserName, time(), 'text', $scene_id);
    echo $resultStr;
  }
  //取消关注事件
  private function doUnsubscribe($postObj)
  {
    //删除绑定的用户信息，可以记录用户取消关注的时间
    $data = $postObj->FromUserName.'在'.date('Y-m-d H:i:s',time()).'取消了关注';
    file_put_contents('list.txt',$data,FILE_APPEND);
  }
  //封装请求方法
  public function request($url,$https=true,$method='get',$data=null)
  {
    //1.初始化
    $ch = curl_init($url);
    //2.设置参数
    //返回数据不直接输出，保存起来
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //判断请求协议
    if($https === true){
      //绕过ssl证书
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    //判断请求方式
    if($method === 'post'){
      //post设置
      curl_setopt($ch, CURLOPT_POST, true);
      //post数据传输
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    //3.发送请求
    $content = curl_exec($ch);
    //4.关闭链接
    curl_close($ch);
    //返回数据
    return $content;
  }
  //获取access_teken
  public function getAccessToken()
  {
    //连接memcache都数据
    $mem = new Memcache();
    $mem->connect('127.0.0.1',11211);
    $access_token = $mem->get('access_token');
    // var_dump($access_token);die();
    if($access_token === false){
      //没有缓存，取远程数据
      //1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
      //2.判断方式
      //3.发送请求
      $content = $this->request($url);
      //4.处理返回值
      $access_token = json_decode($content)->access_token;
      //存储内存缓存
      $mem->set('access_token',$access_token,0,7100);
      // echo file_put_contents('./accesstoken.txt',$access_token);
    }
    return $access_token;
  }
  //获取ticket
  public function getTicket($tmp=true)
  {
    //1.url
    $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
    //2.请求方式
    //判断生成临时还是永久
    if($tmp === true){
      $data = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}';
    }else{
      $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}';
    }
    //3.发送请求
    //public function request($url,$https=true,$method='get',$data=null)
    $content = $this->request($url,true,'post',$data);
    // var_dump($content);die();
    //4.处理返回值
    $ticket = json_decode($content)->ticket;
    return $ticket;
  }
  //通过tiket换取二维码
  public function getQRCode()
  {
    $ticket = $this->getTicket();
    //1.url
    $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
    // $url = 'https://img.alicdn.com/tfs/TB1t9v9RFXXXXaCXXXXXXXXXXXX-360-280.jpg_180x180q90.jpg';
    //2.请求方式
    //3.发送请求
    $content = $this->request($url);
    //4.处理返回值
    //输出声明
    // header('Content-Type:image/jpg');
    // echo $content;
    echo file_put_contents(time().'.jpg',$content);
  }
  //获取用户openID列表
  public function getUserList()
  {
    //1.url
    $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->getAccessToken();
    //2.请求方式
    //3.发送请求
    $content = $this->request($url);
    //4.处理返回值
    $content = json_decode($content);
    $UserArray = $content->data->openid;
    $UserList = '"';
    foreach ($UserArray as $key => $value) {
      $UserList .= $value.'","';
    }
    $UserList = rtrim($UserList,',"').'"';
    return $UserList;
    // echo '关注用户数:'.$content->total.'<br />';
    // echo '本次拉取数:'.$content->total.'<br />';
    // foreach ($content->data->openid as $key => $value) {
    //   echo ($key+1).'###'.$value.'<br />';
    // }
    // var_dump($UserList);
  }
  //通过openID获取用户基本信息
  public function getUserInfo()
  {
    $openid = 'oGMVlw2BFUYpQ6mHUQaD-ukJTVq4';
    //1.url
    $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$openid.'&lang=zh_CN';
    //2.请求方式
    //3.发送请求
    $content = $this->request($url);
    //4.处理返回值
    $content = json_decode($content);
    //替换性别
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
    // var_dump($content);die;
    echo '昵称为:'.$content->nickname.'<br />';
    echo '性别为:'.$sex.'<br />';
    echo '省份为:'.$content->province.'<br />';
    echo '关注时间:'.date('Y-m-d H:i:s',$content->subscribe_time).'<br />';
    echo '<img src="'.$content->headimgurl.'" style="width:100px;" /><br />';
  }
  //通过素材接口上传临时素材
  public function uploadFile()
  {
    //1.url
    $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->getAccessToken().'&type=image';
    //2.请求方式
    $data = array(
      'media' => '@D:\phpStudy\WWW\wechat\1502421960.jpg',
      );
    //3.发送请求
    $content = $this->request($url,true,'post',$data);
    //4.处理返回值
    $content = json_decode($content);
    var_dump($content);die;
  }
  //通过素材接口下载素材
  public function getFile(){
    $media_id = 'JK31gej5wLdEQjNmFHxoPORBMSj3or0_vXdTy-BK8xhaB3b3XI8ztrV-mLQLaTcR';
    //1.url
    $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getAccessToken().'&media_id='.$media_id;
    //2.请求方式
    //3.发送请求
    $content = $this->request($url);
    //4.处理返回值
    echo file_put_contents(time().'.jpg',$content);
  }
  //创建自定义菜单
  public function createMenu()
  {
    //1.url
    $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessToken();
    //2.请求方式
    $data = '{
            "button":[
            {
                 "type":"click",
                 "name":"资讯信息",
                 "key":"news"
            },
            {
                  "name":"php59",
                  "sub_button":[
                   {
                        "type":"view",
                        "name":"测试扫一扫jssdk",
                        "url":"http://47.93.99.154/wechat/jssdk/sample.php"
                   },
                   {
                        "name": "发送位置",
                        "type": "location_select",
                        "key": "rselfmenu_2_0"
                   }]
            }]
        }';
    //3.发送请求
    $content = $this->request($url,true,'post',$data);
    //4.处理返回值
    $content = json_decode($content);
    if($content->errcode == '0'){
      echo '创建菜单成功！';
    }else{
      echo '错误代码为:'.$content->errcode.'<br />';
      echo '错误信息为:'.$content->errmsg.'<br />';
    }
  }
  //查看菜单信息
  public function showMenu()
  {
    //1.url
    $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$this->getAccessToken();
    //2.请求方式
    //3.发送请求
    $content = $this->request($url);
    //4.处理返回值
    var_dump($content);
  }
  //删除菜单
  public function delMenu()
  {
      //1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$this->getAccessToken();
      //2.请求方式
      //3.发送请求
      $content = $this->request($url);
      //4.处理返回值
      // var_dump($content);
      $content = json_decode($content);
      if($content->errcode == '0'){
        echo '删除菜单成功！';
      }else{
        echo '错误代码为:'.$content->errcode.'<br />';
        echo '错误信息为:'.$content->errmsg.'<br />';
      }
  }
  //自定义菜单点击事件
  public function doClick($postObj)
  {
    //不同的key进行不同方法操作
    switch ($postObj->EventKey) {
      case 'news':
        $this->sendNews($postObj);   //news key 发送新闻信息
        break;
      default:
        # code...
        break;
    }
  }
  //发送新闻信息
  public function sendNews($postObj)
  {
    //拼接每一条新闻
    //从数据库取消息
    //多条数据二维数组
    $itemsArray = array(
        array(
            'Title' => '罗杰斯杯NO.1出局 沃兹鏖战三盘进四强',
            'Description' => '当地时间2017年8月11日，加拿大多伦多，2017年WTA罗杰斯杯女单1/4决赛，女单新科世界第一普利斯科娃1-2不敌沃兹尼亚奇',
            'PicUrl' => 'http://p9.pstatp.com/origin/363800087f40c10e1f88',
            'Url' => 'http://www.toutiao.com/a6453200969123971342/#p=1',
          ),
        array(
            'Title' => '罗杰斯杯NO.1出局 沃兹鏖战三盘进四强',
            'Description' => '当地时间2017年8月11日，加拿大多伦多，2017年WTA罗杰斯杯女单1/4决赛，女单新科世界第一普利斯科娃1-2不敌沃兹尼亚奇',
            'PicUrl' => 'http://p9.pstatp.com/origin/363800087f40c10e1f88',
            'Url' => 'http://www.toutiao.com/a6453200969123971342/#p=1',
          ),
        array(
            'Title' => '罗杰斯杯NO.1出局 沃兹鏖战三盘进四强',
            'Description' => '当地时间2017年8月11日，加拿大多伦多，2017年WTA罗杰斯杯女单1/4决赛，女单新科世界第一普利斯科娃1-2不敌沃兹尼亚奇',
            'PicUrl' => 'http://p9.pstatp.com/origin/363800087f40c10e1f88',
            'Url' => 'http://www.toutiao.com/a6453200969123971342/#p=1',
          ),
      );
    //定义空文章变量
    $Articles = '';
    //遍历拼接文章
    foreach ($itemsArray as $key => $value) {
      $Articles .= sprintf($this->itemTpl,$value['Title'],$value['Description'],$value['PicUrl'],$value['Url']);
    }
    $resultStr = sprintf($this->newsTpl,$postObj->FromUserName,$postObj->ToUserName,time(),count($itemsArray),$Articles);
    file_put_contents('return.xml',$resultStr);
    echo $resultStr;
  }
  //发送音乐信息
  public function sendMusic($postObj)
  {
    $Title = '远走高飞';
    $Description = '金志文';
    $MusicUrl = 'http://47.93.99.154/wechat/yzgf.mp3';
    $HQMusicUrl = $MusicUrl;
    $ThumbMediaId = 'JK31gej5wLdEQjNmFHxoPORBMSj3or0_vXdTy-BK8xhaB3b3XI8ztrV-mLQLaTcR';
    echo sprintf($this->musicTpl,$postObj->FromUserName,$postObj->ToUserName,time(),$Title,$Description,$MusicUrl,$HQMusicUrl,$ThumbMediaId);
  }
  //通过地图周边搜索获取数据
  public function amapLBS($localtion){
    //1.url
    $url = 'http://restapi.amap.com/v3/place/around?key=442d1a3492b54672efd2f2cea04a7063&location='.$localtion.'&output=xml&radius=10000&types=餐厅';
    //2.请求方式
    //3.发送请求
    $content = $this->request($url,false);
    //4.处理返回值
    $content = simplexml_load_string($content);
    $info = $content->pois->poi[0];
    // var_dump($info);
    return "推荐餐厅名称为:".$info->name."\r营业类型:".$info->type."\r地址:".$info->address;
  }
  //客服接口发送消息
  public function send()
  {
    //视频素材id
    $mediaID = 'U99qlhNzI06NrryR0oWLNd8E6JnQC3hCtydryA8vNapoqjxxiOCnCOaKnscaZAhJ';
    //1.url
    $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->getAccessToken();
    //授权链接
    // $str = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri=http://47.93.99.154/wechat/getUserInfo.php&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
    $str = 'http://47.93.99.154/wechat/jssdk/sample.php';
    //2.请求方式
    //media_id  视频的
    //thumb_media_id  显示图片的
    //发送文本消息
    $data = '{
            "touser":"oGMVlw2BFUYpQ6mHUQaD-ukJTVq4",
            "msgtype":"text",
            "text":
            {
                 "content":"'.$str.'"
            }
            }';
            // $data = '{
    //         "touser":"oGMVlw2BFUYpQ6mHUQaD-ukJTVq4",
    //         "msgtype":"video",
    //         "video":
    //         {
    //           "media_id":"'.$mediaID.'",
    //           "thumb_media_id":"JK31gej5wLdEQjNmFHxoPORBMSj3or0_vXdTy-BK8xhaB3b3XI8ztrV-mLQLaTcR",
    //           "title":"php59测试视频",
    //           "description":"现在上课的场景"
    //         }
    //       }';
    //3.发送请求
    $content = $this->request($url,true,'post',$data);
    //4.处理返回值
    var_dump($content);
  }
  //高级群发接口
  public function sendAll()
  {
    //1.url
    $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->getAccessToken();
    $media_id = '1KEoT5eBktGqxEsBHCHVz2_qPp5sTvM9urzubujjFTa0U1g73y9b8wU0X86CFCjT';
    $openidList = $this->getUserList();
    //2.请求方式
    $data = '{
           "touser":[
            '.$openidList.'
           ],
           "image":{
              "media_id":"'.$media_id.'"
           },
            "msgtype":"image"
           }';
    //3.发送请求
    $content = $this->request($url,true,'post',$data);
    //4.处理返回值
    var_dump($content);
  }
  // //客服接口发送消息
  // public function send()
  // {
  //   $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->getAccessToken();
  //   // $str = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri=http://47.93.99.154/wechat/userinfo.php&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
  //   $str = 'http://47.93.99.154/wechat/jssdk/sample.php';
  //   $data = '{
  //   "touser":"oGMVlw2BFUYpQ6mHUQaD-ukJTVq4",
  //   "msgtype":"text",
  //   "text":
  //   {
  //        "content":"'.$str.'"
  //   }
  //   }';
  //   $this->request($url,true,'post',$data);
  // }
}