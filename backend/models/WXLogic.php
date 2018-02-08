<?php
namespace backend\models;

use common\components\wx\Base\WXEvent;
use common\components\wx\Base\WXLogicInterface;

class WXLogic extends WXEvent implements WXLogicInterface
{
    
    const TPL_ID_ORDER_MSG = 'TPL_ID_ORDER_MSG';
    const TPL_ID_SYS_MSG = 'TPL_ID_SYS_MSG';
    /*
     * \Yii::$app->wx->replyMsg($type, $data,$postObj);
     * replyMsg被动回复消息接口，格式：
     * 1.回复文本消息
     * $type = 'text';
     * $data = 文本内容，可带超链接文本
     *
     * 2.回复图片消息
     * $type = 'image';
     * $data = 已上传至公众号的MediaID
     *
     * 3.回复语音消息
     * $type = 'voice';
     * $data = 已上传至公众号的MediaID
     *
     * 4.回复视频消息
     * $type = 'video';
     * $date = array(
     * 'MediaId' => 已上传至公众号的MediaID
     * 'Title' => 视频标题
     * 'Description' => 视频描述
     * )
     *
     * 5.回复音乐信息
     * $type = 'music';
     * $date = array(
     * 'Title' => 音乐标题
     * 'Description' => 音乐描述
     * 'MusicUrl' => 音乐地址
     * 'HQMusicUrl' => 高保真音乐地址
     * 'ThumbMediaId' => 缩略图ID，已上传至公众号
     * )
     *
     * 6.回复图文消息
     * $type = 'news';
     * $date = array();
     * $item = array(
     * 'Title' => 图文标题
     * 'Description' => 图文描述
     * 'PicUrl' => 图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200
     * 'Url' => 图文链接
     * );
     * $data['Articles'][] = $item;
     *
     * 多条图文消息信息，默认第一个item为大图,注意，如果图文数超过10，则将会无响应
     * 该接口仅为生成图文列表，并不产生实际的文章与链接
     *
     */
    public function callback_text($postObj)
    {
        $this->news1($postObj);
    }

    public function callback_image($postObj)
    {}

    public function callback_voice($postObj)
    {}

    public function callback_video($postObj)
    {}

    public function callback_shortvideo($postObj)
    {}

    public function callback_location($postObj)
    {}

    public function callback_subscribe($postObj)
    {
        $this->news1($postObj);
    }

    public function callback_unsubscribe($postObj)
    {}

    public function callback_scan($postObj)
    {}

    public function callback_e_location($postObj)
    {}

    public function callback_click($postObj)
    {
        switch ($postObj->EventKey) {
            case "SYSTEM_INFO_MSG":
                $this->news1($postObj);
                break;
        }
    }

    public function callback_view($postObj)
    {}

    public function news1($postObj)
    {
    /*    $data = [];
        \Yii::$app->wx->replyMsg("news", $data, $postObj);*/
        //普通文案回复相关
        \Yii::$app->wx->replyMsg('text', 'ffff', $postObj);
    }
    
    public static function getTpl($type){
        return \Yii::$app->wx->getTpl($type);
    }
    
    public static function setTpl($type, $tpl_id){
        return \Yii::$app->wx->setConfigValue($type, $tpl_id);
    }
    
}