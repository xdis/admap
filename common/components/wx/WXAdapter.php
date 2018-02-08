<?php
namespace common\components\wx;

use common\helper\Logger;
use common\helper\CommonTools;
use common\components\wx\Model\WXDAO;
use common\components\wx\Base\WXEvent;
use common\components\wx\Base\WXLogicInterface;
use common\helper\HttpComponent;

class WXAdapter extends WXEvent
{

    public $logicClass = null;  
    public $test_openid = null;
    
    /**
     * 微信链接验证TOKEN
     */
    public $wx_token = "";
    /**
     * 公众号APPID
     */
    public $wx_appid = "";
    /**
     * 公众号密码
     */
    public $wx_secret = "";
    public $wx_img_path = "/data/img/wx/";
    public $wx_menu = "menu.json";
    
    private $_logicObj = null;
    private $logger = null;
    private $expire_time = 7000;

    public function init()
    {
        parent::init();
        if (! session_id()) {
            session_start();
        }
        $this->logger = Logger::getLogger("wx_srv.log");
        
        if ($this->logicClass === null) {
            throw new \Exception('WXAdapter::logicClass must be set.');
        }
        $this->_logicObj = new $this->logicClass();
        if (! $this->_logicObj instanceof WXLogicInterface) {
            throw new \Exception("The class must be an object implementing logicClass.");
        }
        if (! $this->_logicObj instanceof WXEvent) {
            throw new \Exception("The class must be an object extends WXEvent.");
        }
        
        $event_lst = [
            'text' => 'callback_text',
            'image' => 'callback_image',
            'voice' => 'callback_voice',
            'video' => 'callback_video',
            'shortvideo' => 'callback_shortvideo',
            'location' => 'callback_location',
            'subscribe' => 'callback_subscribe',
            'unsubscribe' => 'callback_unsubscribe',
            'SCAN' => 'callback_scan',
            'LOCATION' => 'callback_e_location',
            'CLICK' => 'callback_click',
            'VIEW' => 'callback_view',
        ];
        
        $this->_logicObj->listenArray($this, $event_lst);
    }

    /**
     * 构建素材的mediaId地址
     *
     * @param $mediaId
     * @return string
     */
    public function getTemporaryMaterialUrl($mediaId)
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=" . self::getAccessToken() . "&media_id=" . $mediaId;
        return $wx_url;
    }

    /**
     * 微信接口验证
     */
    public function valid()
    {
        if (isset($_GET["echostr"]) && $this->checkSignature()) {
            echo $_GET["echostr"];
            exit();
        }
    }

    private function checkSignature()
    {
        if (isset($_GET["signature"])) {
            $signature = $_GET["signature"];
            $timestamp = $_GET["timestamp"];
            $nonce = $_GET["nonce"];
            
            $tmpArr = array(
                $this->wx_token,
                $timestamp,
                $nonce
            );
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode($tmpArr);
            $tmpStr = sha1($tmpStr);
            
            if ($tmpStr == $signature) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 接收微信消息事件
     */
    public function responseMsg()
    {
        if(isset($_GET["echostr"])){
            $this->valid();
        }else{
            $postStr = file_get_contents("php://input");
        }
        if (isset($postStr) && ! empty($postStr)) {
            $this->logger->debug("WXAapter recive data :" . $postStr);
            // 响应微信服务器，接收到信息后，先回复空字符串，不然5秒后会有重复信息
            echo "";
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $msgType = (string) $postObj->MsgType;
            $this->logger->debug("MsgType ==>> " . $msgType);
            // 更新用户激活时间
            $this->updateUserInfo($postObj->FromUserName);
            // 根据消息类型分发处理
            if ($msgType == 'event') {
                $this->responseEvent($postObj);
            } else {
                $this->trigger($msgType, array(
                    $postObj
                ));
            }
        } else {
            $this->logger->debug("Recived Data is NULL...");
        }
    }

    //接收文本消息
    private function receiveText($object)
    {
         $result = $this->transmitService($object);
         return $result;
    }
    
    //回复多客服消息
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[transfer_customer_service]]></MsgType>
                        <TransInfo>
                               <KfAccount><![CDATA[test7@yuntumedia]]></KfAccount>
                        </TransInfo>
                        </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
    
    /**
     * 处理消息分发事件
     *
     * @param $postObj 传过来的消息对象            
     */
    public function responseEvent($postObj)
    {
        $eventType = (string) $postObj->Event;
        if ($eventType == "unsubscribe") {
            WXDAO::unsubscribe($postObj->FromUserName);
        }
        $createTime = date("Y-m-d H:i:s", (string) $postObj->CreateTime);
        // 根据消息类型分发处理
        $this->trigger($eventType, array(
            $postObj
        ));
    }

    /**
     * 获取accesstoken
     * @param string $type            
     * @return mixed
     */
    public function getAccessToken($type = self::WX_ACCESS_TOKEN)
    {
        $wx_token_data = $this->getTokenData($type);
        return $wx_token_data['wx_value'];
    }
    
    /**
     * 更新Token
     * @param string $type
     */
    public function refreshToken($type = self::WX_ACCESS_TOKEN){
        $wx_token_data = $this->getTokenData($type);
        $jsonObj = $this->request_get($wx_token_data['wx_url'], 'json');
        $wx_token_data['wx_result'] = $jsonObj;
        $wx_token_data['wx_value'] = $jsonObj[$wx_token_data['wx_key']];
        if ($wx_token_data['wx_value']) {
            $wx_token_data['wx_time'] = time();
            $_SESSION[$type] = $wx_token_data;
        } else {
            $this->logger->debug($jsonObj);
        }
        return $wx_token_data;
    }

    /**
     * 获取Token
     * @param string $type
     */
    private function getTokenData($type = self::WX_ACCESS_TOKEN){
        //查看session是否有值，有的话直接返回
        if (isset($_SESSION[$type]) && $this->checkTokenData($_SESSION[$type])) {
            $wx_token_data = $_SESSION[$type];
        } else {
            $wx_token_data = $this->getTokenUrl($type);
            $jsonObj = $this->request_get($wx_token_data['wx_url'], 'json');
            $this->logger->debug("getTokenData===" . json_encode($wx_token_data['wx_url'],true));
            $this->logger->debug("getTokenData===" . json_encode($jsonObj,true));
            $wx_token_data['wx_result'] = $jsonObj;
            $wx_token_data['wx_value'] = $jsonObj[$wx_token_data['wx_key']];
            $wx_token_data['wx_time'] = time();
        }
        return $wx_token_data;
    }

    //拼接微信请求地址orther
    public function getTokenUrl($type){
        $wx_token_data = array();
        $wx_token_data['wx_time'] = 0;
        switch ($type) {
            case self::WX_JSAPI_TICKET:
                $wx_token_data['wx_url'] = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $this->getAccessToken() . "&type=jsapi";
                $wx_token_data['wx_item'] = $type;
                $wx_token_data['wx_key'] = "ticket";
                break;
            case self::WX_ACCESS_TOKEN:
            default:
                $wx_token_data['wx_url'] = sprintf("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s", $this->wx_appid, $this->wx_secret);
                $wx_token_data['wx_item'] = $type;
                $wx_token_data['wx_key'] = "access_token";
                break;
        }
        return $wx_token_data;
    }
    
    private function checkTokenData($wx_token_data){
        return isset($wx_token_data['wx_url']) && isset($wx_token_data['wx_item']) && isset($wx_token_data['wx_key']) && isset($wx_token_data['wx_time']);
    }

    /**
     * 
     * @return string
     */
    public function getJSApiTicket()
    {
        return $this->getAccessToken(self::WX_JSAPI_TICKET);
    }
    
    /**
     * 根据用户ID获取openid
     * 
     * @param string $uid
     * @return string
     */
    public function getOpenidByID($uid){
        $openid = 0;
        $wx = $this->getUserInfoByID($uid);
        if($wx){
            $openid = $wx['open_id'];
        }
        return $openid;
    }
    
    /**
     * 根据用户ID获取微信用户信息
     * 
     * @param string $uid
     */
    public function getUserInfoByID($uid){
        return WXDAO::getUserByID($uid);
    }

    /**
     * 获取当前用户OpenID
     *
     * @return mixed
     */
    public function getUserOpenID()
    {
        if($this->test_openid != null){
            return $this->test_openid;
        }
        // 查找session中是否存在wx用户信息，否则启动Oauth2授权
        if (isset($_SESSION["wx_user_data"])) {
            $wx_user_data = $_SESSION["wx_user_data"];
            return $wx_user_data['openid'];
        } else {
            if (isset($_GET['code'])) {
                $jsonObj = json_decode($this->oauth2accesstoken($_GET['code']), true);
                $openid = $jsonObj['openid'];
                if ($openid) {
                    $_SESSION["wx_user_data"] = $jsonObj;
                    $redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
                    if ($_GET['state']) {
                        $redirect_url = $redirect_url . "#" . $_GET['state'];
                        header("location: " . $redirect_url);
                        exit();
                    }
                    return $openid;
                }
            } else {
                $redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $this->oauth2authorize($redirect_url);
            }
        }
    }
    
    /**
     * oauth2验证第一次握手请求
     * @param $redirect_url 回调地址
     * @param string $state 状态码
     */
    private function oauth2authorize($redirect_url, $state = "")
    {
        $wx_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $this->wx_appid . "&redirect_uri=" . $redirect_url . "&response_type=code&scope=snsapi_base&state=" . $state . "#wechat_redirect";
        header("location: " . $wx_url, true, 302);
        exit();
    }
    
    /**
     * oauth2验证第二次握手请求
     *
     * @param $code
     * @return string
     */
    private function oauth2accesstoken($code)
    {
        $wx_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->wx_appid . "&secret=" . $this->wx_secret . "&code=" . $code . "&grant_type=authorization_code";
        $str_result = file_get_contents($wx_url);
        return $str_result;
    }

    /**
     * 获取用户ID
     * @return NULL
     */
    public function getUserID()
    {
        $openid = $this->getUserOpenID();
        return WXDAO::getUseridByOpenid($openid);
    }

    /**
     * 绑定用户ID
     * @param string $uid
     */
    public function updateUserid($uid)
    {
        $openid = $this->getUserOpenID();
        WXDAO::updateUserid($uid, $openid);
    }

    /**
     * 根据OpenID获取用户信息
     *
     * @param $openid
     * @return string
     */
    public function getUserInfo($openid)
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $this->getAccessToken() . "&openid=" . $openid . "&lang=zh_CN";
        $str_result = file_get_contents($wx_url);
        return $str_result;
    }

    /**
     * 创建二维码
     *
     * @param $dataid 数据ID            
     * @param $datatype $dataid所属类型            
     * @param $type 临时或永久二维码            
     * @param int $expire_seconds 过期时间
     * 
     * @return mixed
     */
    public function createQRCode($dataid, $datatype, $type = self::QR_LIMIT_SCENE, $expire_seconds = 604800)
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->getAccessToken();
        $arrData = array();
        $arrData['action_name'] = $type;
        $tmp = false;
        if ($type == self::QR_SCENE) {
            $arrData['expire_seconds'] = $expire_seconds;
            $tmp = true;
        }
        $scene_id = WXDAO::getUnusedQRCode($tmp);
        
        $arrData['action_info']['scene']['scene_id'] = $scene_id;
        
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $arrData);
        $jsonObj = json_decode($return_content, true);
        $imgurl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $jsonObj['ticket'];
        
        $local_url = $this->wx_img_path . 'qr/';
        $filename = CommonTools::downImg($imgurl, $local_url);
        $qr = array(
            'id' => $scene_id,
            'data_id' => $dataid,
            'type' => $datatype,
            'ticket' => $jsonObj['ticket'],
            'img_url' => $imgurl,
            'img_link' => $jsonObj['url'],
            'img_url_local' => $filename,
            'add_time' => time(),
            'end_time' => time() + $expire_seconds
        );
        WXDAO::saveQRCode($qr);
        return $return_content;
    }

    /**
     * 微信消息被动接收处理
     *
     * @param $type 事件类型       
     * @param $data 发送数据            
     */
    public function replyMsg($type, $data, $postObj)
    {
        $xml_arr = array();
        $data_arr = array();
        $data_arr['ToUserName'] = $postObj->FromUserName;
        $data_arr['FromUserName'] = $postObj->ToUserName;
        $data_arr['CreateTime'] = time();
        $data_arr['MsgType'] = $type;
        $this->logger->debug("Reply===" . json_encode($data_arr,true));
        switch ($type) {
            case 'text':
                $data_arr['Content'] = $data;
                break;
            case 'image':
                $data_arr['Image']['MediaId'] = $data;
                break;
            case 'voice':
                $data_arr['Voice']['MediaId'] = $data;
                break;
            case 'video':
                $data_arr['Video']['MediaId'] = $data['MediaId'];
                $data_arr['Video']['Title'] = $data['Title'];
                $data_arr['Video']['Description'] = $data['Description'];
                break;
            case 'music':
                $data_arr['Music']['Title'] = $data['Title'];
                $data_arr['Music']['Description'] = $data['Description'];
                $data_arr['Music']['MusicUrl'] = $data['MusicUrl'];
                $data_arr['Music']['HQMusicUrl'] = $data['HQMusicUrl'];
                $data_arr['Music']['ThumbMediaId'] = $data['ThumbMediaId'];
                break;
            case 'news':
                $data_arr['ArticleCount'] = count($data['Articles']);
                foreach ($data['Articles'] as $v) {
                    $item = array();
                    $item['item']['Title'] = $v['Title'];
                    $item['item']['Description'] = $v['Description'];
                    $item['item']['PicUrl'] = $v['PicUrl'];
                    $item['item']['Url'] = $v['Url'];
                    $data_arr['Articles'][] = $item;
                }
                break;
        }
        $xml_arr['xml'] = $data_arr;
        $resultStr = $this->json2XML($xml_arr);
        echo $resultStr;
    }

    /**
     * 主动发送消息
     * @param $openid 用户OpenID            
     * @param $type 消息类型            
     * @param $content 消息内容            
     * @param null $customservice            
     * @return mixed
     */
    public function sendMsg($openid, $type, $content)
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->getAccessToken();
        $textTpl = null;
        $textTpl =
            "{
                \"touser\": \"$openid\",
                \"msgtype\": \"$type\",
                \"text\": {
                \"content\": \"$content\"
                }
            }";
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $textTpl);
        return $return_content;
        /*
        //圖片tQ0txHAyrA-OMogt03iZ__wdCQyTuDszOcfEEL-_Xo8
        $wx_url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->getAccessToken();
        $textTpl = null;
        
        $textTpl =
        "{
        \"touser\": \"$openid\",
        \"msgtype\": \"$type\",
        \"image\": {
        \"media_id\": \"$content\"
        }
        }";
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $textTpl);
        return $return_content;
        */
    }

    /**
     * 使用模板主动发送消息
     *
     * @param $openid 用户OpenID            
     * @param $tpl_id 模板ID            
     * @param $tpl_content 模板内容            
     * @param $url 点击内容对应的链接            
     * @param string $color 内容字体颜色
     * @return mixed
     */
    public function sendTplMsg($openid, $tpl_id, $tpl_content, $url = '', $color = "#FF0000")
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->getAccessToken();
        $textTpl = array(
            "touser" => $openid,
            "template_id" => $tpl_id,
            "url" => $url,
            "topcolor" => $color,
            "data" => $tpl_content
        );
        
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $textTpl);
        return $return_content;
    }

    /**
     * 上传mediaID
     */
    public function uploadMedia2($type, $file_path)
    {
        $type = 'thumb';
        $file_path = \Yii::getAlias('@www').'/web/img/top.jpg';
        $wx_url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $this->getAccessToken() . "&type=" . $type;
        $sendData = array('media' => '@' . realpath($file_path));;
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $sendData,'form-data');
        return $return_content;
    }
    
    /**
     *  上传的临时文件，服务3天后自动删除,图片大小不超迿M，支持bmp/png/jpeg/jpg/gif格式＿
     *  语音大小不超迿M，长度不超过60秒，支持mp3/wma/wav/amr格式
     */
    function uploadMedia($filepath,$type){
        $type = 'thumb';
        $filepath = \Yii::getAlias('@www').'/web/img/top.jpg';
        define('UPLOAD_MEDIA_URL','https://api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s');
        $url = sprintf(UPLOAD_MEDIA_URL, $this->getAccessToken(),$type);
        $httpComp = new HttpComponent($url);
        $httpComp->setContentType('multipart/form-data');
        if (class_exists('\CURLFile')) {
            $data = array('media' => new \CURLFile(realpath($filepath)));
        } else {
            $data = array('media' => '@' . realpath($filepath));
        }
        $httpComp->setPost($data);
        $httpComp->send_request();
        $result = json_decode($httpComp->getHttpResult(),true);
        return $result;
    }
    
    /**
     * 上传永久mediaID
     * media_id: "tQ0txHAyrA-OMogt03iZ_wiwbrSpM1uwXaItg29me1E"
     url: "https://mmbiz.qlogo.cn/mmbiz/csaS1YBzf2BhQeKzuyQYtOtNUH9TCPfrqyhXTaWdZfSLIjaY9DQKdT4Z64xGzK8Hkicp33RibDMbayMLGmr1OxxA/0?wx_fmt=jpeg"
     */
    public function uploadMediaLong()
    {
         $type = 'thumb';
         $filepath = \Yii::getAlias('@www').'/web/img/top.jpg';
         define('ADD_MATERIAL_URL','http://api.weixin.qq.com/cgi-bin/material/add_material?access_token=%s');
         $url = sprintf(ADD_MATERIAL_URL,$this->getAccessToken());
         $httpComp = new HttpComponent($url,120);
         $httpComp->setContentType('multipart/form-data');
         if (class_exists('\CURLFile')) {
             $data = array('media' => new \CURLFile(realpath($filepath)),'type'=>$type);
         } else {
             $data = array('media' => '@' . realpath($filepath),'type'=>$type);
         }
         $httpComp->setPost($data);
         $httpComp->send_request();
         $result = json_decode($httpComp->getHttpResult(),true);
         return $result;
    }
    
    /*
     * 上传永久图文素材
    */
    public function uploadTextImage()
    {
        //media_id = 'tQ0txHAyrA-OMogt03iZ_8Jpfhjjm5_xeEETkCDHV50';
        $title = '测试';
        $template_id = 'tQ0txHAyrA-OMogt03iZ_wiwbrSpM1uwXaItg29me1E';
        $author = '啦啦啦';
        $digest = '测试';
        $show_cover_pic ='1';
        $content =  '
             <a href="https://www.baidu.com/"><img src="https://mmbiz.qlogo.cn/mmbiz/csaS1YBzf2BhQeKzuyQYtOtNUH9TCPfrqyhXTaWdZfSLIjaY9DQKdT4Z64xGzK8Hkicp33RibDMbayMLGmr1OxxA/0?wx_fmt=jpeg"/></a>
            <br/>
            <br/>
            <a href="https://www.baidu.com/">https://www.baidu.com/</a>
            <br/>
            <a href="http://v.qq.com/">http://v.qq.com/</a>
            <span style="font-size:24px;background-color:#FF9900;">重大好消息</span>
            </p>
            ';
        $content_source_url = 'http://192.168.1.38/yuntuwx/www/web/site/content/72';
        $jsonArr = array(
            "articles"=> array(
                array(
                    "title"=> $title,
                    "thumb_media_id"=> $template_id,
                    "author"=> $author,
                    "digest"=> $digest,
                    "show_cover_pic"=>$show_cover_pic,
                    "content"=> $content,
                    "content_source_url"=> $content_source_url,
                )
            ),
        );
        $wx_url = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=" . $this->getAccessToken();
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url,$jsonArr);
        return $return_content;
    }
    
    /**
              根据分组进行群发 (订阅号与服务号认证后均可用)
     */
    public function massAll()
    {
        $type = 'thumb';
        $filepath = \Yii::getAlias('@www').'/web/img/top.jpg';
        define('MASS_All','https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=%s');
        $url = sprintf(MASS_All,$this->getAccessToken());
        $httpComp = new HttpComponent($url,120);
        $httpComp->setContentType('multipart/form-data');
        if (class_exists('\CURLFile')) {
            $data = array('media' => new \CURLFile(realpath($filepath)),'type'=>$type);
        } else {
            $data = array('media' => '@' . realpath($filepath),'type'=>$type);
        }
        $httpComp->setPost($data);
        $httpComp->send_request();
        $result = json_decode($httpComp->getHttpResult(),true);
        return $result;
    }
    
    /**
             根据OpenID列表群发 (订阅号不可用, 服务号认证后可用)
     */
    public function massOpenIdData()
    {
        $data = [
           'touser' => [' o5KAYs_FYuaPL15MeX-_uxpXa4_0',
           'o5KAYs0XySJhatW_eF9OoQvRabt0'],
                'mpnews' => array(
                    'media_id' => 'tQ0txHAyrA-OMogt03iZ_8Jpfhjjm5_xeEETkCDHV50',
                ),
            
               'msgtype' => 'mpnews',
                'title' => '测试',
                'description' => '测试openid群发',
               'thumb_media_id' => 'tQ0txHAyrA-OMogt03iZ_wiwbrSpM1uwXaItg29me1E',
        ];
        $wx_url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . $this->getAccessToken();
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $data);
        return $return_content;
    }
    
    
    /**
     * 获取永久素材
     */
    public function getMediaLong()
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=" . $this->getAccessToken();
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url);
        return $return_content;
    }

    /**
     * 更新自定义菜单
     *
     * @return mixed
     */
    public function updateMenu($menu)
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->getAccessToken();
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $menu);
        return $return_content;
    }
    
    public function getUserList($next_openid = null)
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->getAccessToken();
        if($next_openid){
            $wx_url .= "&next_openid=" . $next_openid;
        }
        $str_result = file_get_contents($wx_url);
        return $str_result;
    }
    
    public function updateUserInfo($openid){
        $userinfo = json_decode($this->getUserInfo($openid), true);
        return WXDAO::saveWxUser($userinfo);
    }
    
    public function saveUserList($openid_list, $all){
        WXDAO::addUserList($openid_list);
        $openid_list = WXDAO::getUpdateList($all);
        foreach ($openid_list as $openid){
            $this->updateUserInfo($openid);
        }
    }
    
    public function updateUserList($all = true, $next_openid = null){
        set_time_limit(0);
        $user_list = json_decode($this->getUserList($next_openid), true);
        $this->saveUserList($user_list['data']['openid'], $all);
        if($user_list['count'] == 10000){
            $this->updateUserList($all, $user_list['next_openid']);
        }
    }
    
    public function getTpl($type){
        $config = WXDAO::getConfig($this->wx_appid, $type);
        if ($config) {
            $tpl = WXDAO::getTpl($config['item_value']);
            if($tpl){
                preg_match_all("/{{(.*)\.DATA}}/", $tpl['content'], $matchs);
                $arr = [];
                foreach ($matchs[1] as $key){
                    $arr[$key] = [
                        'value' => '',
                        'color' => '#173177'
                    ];
                }
                $tpl['tpl_arr'] = $arr;
            }
            return $tpl;
        }else{
            return null;
        }
    }
    
    public function setConfigValue($item_key, $item_value){
        WXDAO::updateConfig($this->wx_appid, $item_key, $item_value, time());
    }
    
    public function updateTpls()
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=" . $this->getAccessToken();
        $str_result = file_get_contents($wx_url);
        $jsonObj = json_decode($str_result, true);
        if(isset($jsonObj['template_list'])){
            WXDAO::updateTpls($jsonObj['template_list']);
        }
        return $str_result;
    }
    
    public function createTplMsg($openid, $tpl_id, $tpl, $url = '', $topcolor = "#FF0000")
    {
        return WXDAO::createTplMsg($openid, $tpl_id, $tpl, $url, $topcolor);
    }
    
    public function sendWxMsg(){
        $msg_list = WXDAO::getWxMsg();
        foreach ($msg_list as $v ){
            $openid = $v['openid'];
            $tpl_id = $v['tpl_id'];
            $tpl_content = json_decode($v['content'], true);
            $url = $v['url'];
            $color = $v['topcolor'];
            $v['post_time'] = time();
            $ret_val = $this->sendTplMsg($openid, $tpl_id, $tpl_content, $url, $color);
            $ret_val = json_decode($ret_val, true);
            $is_send = false;
            if(isset($ret_val['errcode']) && $ret_val['errcode'] == 0){
                $is_send = true;
            }
            WXDAO::updateMsgStatus($v['id'], $is_send);
        }
    }
    
    public function request_get($url, $format = 'default'){
        list ($return_code, $return_content) = CommonTools::http_request_get($url);
        $return_content = $this->data_format($return_content, $format);
        return $return_content;
    }
    
    public function request_post($url, $data, $format = 'default'){
        list ($return_code, $return_content) = CommonTools::http_post_data($url, $data);
        $return_content = $this->data_format($return_content, $format);
        return $return_content;
    }
    
    public function data_format($data, $format){
        switch ($format){
            case 'json':
                $data = json_decode($data, true);
                break;
            default:
                break;
        }
        return $data;
    }

    /**
     * 数组转为微信接口对应的xml数据
     * @param
     *            $arr
     * @return string
     */
    public function json2XML($arr)
    {
        $ret_str = "";
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr_v = $this->json2XML($v);
                if (! is_integer($k)) {
                    $ret_str = $ret_str . sprintf("<%s>%s</%s>", $k, $arr_v, $k);
                } else {
                    $ret_str = $ret_str . $arr_v;
                }
            } else {
                if (is_integer($v)) {
                    $ret_str = $ret_str . sprintf("<%s>%s</%s>", $k, $v, $k);
                } else {
                    $ret_str = $ret_str . sprintf("<%s><![CDATA[%s]]></%s>", $k, $v, $k);
                }
            }
        }
        return $ret_str;
    }
    
    /**
     * 获取客服基本信息
     */
    public function getkflist()
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=" . $this->getAccessToken();
        list ($return_code, $return_content) = CommonTools::http_request_get($wx_url);
        return $return_content;
    }
    
    /**
     * 获取客服基本信息
     */
    public function getonlinekflist()
    {
        $wx_url = "https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token=" . $this->getAccessToken();
        list ($return_code, $return_content) = CommonTools::http_request_get($wx_url);
        return $return_content;
    }
    
    /**
     * 添加客服帐号
     */
    public function addaccount($kf_account,$nickname)
    {
        $wx_url = "https://api.weixin.qq.com/customservice/kfaccount/add?access_token=" . $this->getAccessToken();
        $textTpl = array(
            "kf_account" => $kf_account,
            "nickname" => $nickname
        );
    
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $textTpl);
        return $return_content;
    }
    
    /**
     * 邀请绑定客服帐号
     */
    public function inviteworker($kf_account,$invite_wx)
    {
        $wx_url = "https://api.weixin.qq.com/customservice/kfaccount/add?access_token=" . $this->getAccessToken();
        $textTpl = array(
            "kf_account" => $kf_account,
            "invite_wx" => $invite_wx
        );
    
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $textTpl);
        return $return_content;
    }
    
    /**
     * 设置客服信息
     */
    public function updateaccount($kf_account,$nickname)
    {
        $wx_url = "https://api.weixin.qq.com/customservice/kfaccount/update?access_token=" . $this->getAccessToken();
        $textTpl = array(
            "kf_account" => $kf_account,
            "nickname" => $nickname
        );
    
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $textTpl);
        return $return_content;
    }
    
    /**
     * 上传客服头像
     */
    public function uploadheadimgaccount($kf_account,$media)
    {
        define('ADD_MATERIAL_URL','https://api.weixin.qq.com/customservice/kfaccount/uploadheadimg?access_token=%s&kf_account=%s');
        $url = sprintf(ADD_MATERIAL_URL,$this->getAccessToken(),$kf_account);
        $httpComp = new HttpComponent($url,120);
        $httpComp->setContentType('multipart/form-data');
        if (class_exists('\CURLFile')) {
            $data = array('media' => new \CURLFile(realpath($media)));
        } else {
            $data = array('media' => '@' . realpath($media));
        }
        $httpComp->setPost($data);
        $httpComp->send_request();
        $result = json_decode($httpComp->getHttpResult(),true);
        return $result;
    }
    
    /**
     * 删除客服帐号
     */
    public function delaccount($kf_account)
    {
        $wx_url = "https://api.weixin.qq.com/customservice/kfaccount/del?access_token=" . $this->getAccessToken().'&kf_account='.$kf_account;
        list ($return_code, $return_content) = CommonTools::http_request_get($wx_url);
        return $return_content;
    }
    
    /**
     * 开启会话
     */
    public function createsession($kf_account,$openid)
    {
        $wx_url = "https://api.weixin.qq.com/customservice/kfsession/create?access_token=" . $this->getAccessToken();
        $textTpl = array(
            "kf_account" => $kf_account,
            "openid" => $openid
        );
    
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $textTpl);
        return $return_content;
    }
    
    /**
     * 关闭会话
     */
    public function closesession($kf_account,$openid)
    {
        $wx_url = "https://api.weixin.qq.com/customservice/kfsession/close?access_token=" . $this->getAccessToken();
        $textTpl = array(
            "kf_account" => $kf_account,
            "openid" => $openid
        );
    
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url, $textTpl);
        return $return_content;
    }
    
    /**
     * 获取客户会话状态
     */
    public function getaccountsession($openid)
    {
        $wx_url = "https://api.weixin.qq.com/customservice/kfsession/getsession?access_token=" . $this->getAccessToken().'&openid='.$openid;
        list ($return_code, $return_content) = CommonTools::http_request_get($wx_url);
        return $return_content;
    }
    
    /**
     * 获取客服会话列表
     */
    public function getsessionlist($kf_account)
    {
        $wx_url = "https://api.weixin.qq.com/customservice/kfsession/getsessionlist?access_token=" . $this->getAccessToken().'&kf_account='.$kf_account;
        list ($return_code, $return_content) = CommonTools::http_request_get($wx_url);
        return $return_content;
    }
    
    /**
     * 获取客服会话列表
     */
    public function getwaitcase()
    {
        $wx_url = "https://api.weixin.qq.com/customservice/kfsession/getwaitcase?access_token=" . $this->getAccessToken();
        list ($return_code, $return_content) = CommonTools::http_request_get($wx_url);
        return $return_content;
    }
    
    /**
     * 获取聊天记录
     */
    public function getmsglist($starttime='1464710400',$endtime='1464796800',$msgid='1',$number='100')
    {
        $wx_url = "https://api.weixin.qq.com/customservice/msgrecord/getmsglist?access_token=" . $this->getAccessToken();
        $textTpl = array(
            "starttime" => $starttime,
            "endtime" => $endtime,
            "msgid" => $msgid,
            "number" => $number
        );
        list ($return_code, $return_content) = CommonTools::http_post_data($wx_url,$textTpl);
        return $return_content;
    }
    
}