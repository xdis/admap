<?php
namespace common\components\wx\Model;

class WXDAO
{

    /*
     * 获取配置文件
     */
    public static function getConfig($appid, $item_key)
    {
        $config = WxConfig::findOne([
            'appid' => $appid,
            'item_key' => $item_key
        ]);
        if ($config) {
            return [
                "appid" => $config->appid,
                "item_key" => $config->item_key,
                "item_value" => $config->item_value,
                "last_update_time" => $config->last_update_time
            ];
        } else {
            return false;
        }
    }

    /*
     * 更新配置文件
     */
    public static function updateConfig($appid, $item_key, $item_value, $last_update_time)
    {
        $config = WxConfig::findOne([
            'appid' => $appid,
            'item_key' => $item_key
        ]);
        if (! $config) {
            $config = new WxConfig();
            $config->appid = $appid;
            $config->item_key = $item_key;
        }
        $config->item_value = $item_value;
        $config->last_update_time = $last_update_time;
        $config->save();
    }

    /*
     * 更新用户登录时间
     */
    public static function updateActiveTime($openid)
    {
        $ret = false;
        $wx_user = WxUser::findOne($openid);
        if ($wx_user) {
            $wx_user->last_active_time = time();
            $wx_user->save();
            $ret = true;
        }
        return $ret;
    }

    public static function unsubscribe($openid)
    {
        $wx_user = WxUser::findOne($openid);
        if ($wx_user) {
            $wx_user->is_subscribe = 0;
            $wx_user->save();
        }
    }
    public static function genWxUser($openid){
        $wx_user = WxUser::findOne($openid);
        if (! $wx_user) {
            $wx_user = new WxUser();
            $wx_user->open_id = $openid;
            $wx_user->id = 0;
            $wx_user->params = 0;
            $wx_user->is_update = 0;
            $wx_user->quit_time = 0;
            $wx_user->add_time = time();
            $wx_user->last_active_time = time();
            $wx_user->save();
        }
        return $wx_user;
    }
    
    public static function addUserList($openid_list){
        foreach ($openid_list as $openid){
            self::genWxUser($openid);
        }
    }
    
    public static function getUpdateList($all){
        $update_list = [];
        if($all){
            WxUser::updateAll(['is_update' => '0']);
            $lst = WxUser::find()->select('open_id')->all();
        }else{
            $lst = WxUser::find()->select('open_id')->where(['is_update' => '0'])->all();
        }
        foreach ($lst as $v){
            $update_list[] = $v['open_id'];
        }
        return $update_list;
    }

    /*
     * 新关注保存资料
     */
    public static function saveWxUser($userinfo)
    {
        $wx_user = self::genWxUser($userinfo['openid']);
        $wx_user->is_subscribe = $userinfo['subscribe'];
        $wx_user->is_update = 1;
        $wx_user->last_update_time = time();
        if($userinfo['subscribe'] != 0){
            $wx_user->nick_name = $userinfo['nickname'];
            $wx_user->sex = $userinfo['sex'];
            $wx_user->lang = $userinfo['language'];
            $wx_user->city = $userinfo['city'];
            $wx_user->province = $userinfo['province'];
            $wx_user->country = $userinfo['country'];
            $wx_user->head_img_url = $userinfo['headimgurl'];
            $wx_user->sub_time = $userinfo['subscribe_time'];
        }
        $wx_user->save();
        return $wx_user->toArray();
    }

    public static function getUseridByOpenid($openid)
    {
        $wx_user = WxUser::findOne($openid);
        return $wx_user ? $wx_user->id : null;
    }

    public static function updateUserid($uid, $openid)
    {
        $wx_user = WxUser::findOne($openid);
        if ($wx_user) {
            $wx_user->id = $uid;
            $wx_user->save();
        }
    }

    public static function getUnusedQRCode($tmp = false)
    {
        $id = 0;
        if ($tmp) {
            $ret = WxQRCode::find()->where([
                '>',
                'id',
                100000
            ])->max('id');
            if ($ret) {
                $id = $ret + 1;
            } else {
                $id = 100001;
            }
        } else {
            $ret = WxQRCode::find()->where([
                '<=',
                'id',
                100000
            ])->max('id');
            if ($ret) {
                $id = $ret + 1;
            } else {
                $id = 1;
            }
        }
        return $id;
    }
    
    public static function saveQRCode($qr){
        $qrcode = WxQRCode::findOne($qr['id']);
        if(!$qrcode){
            $qrcode = new WxQRCode();
        }
        $qrcode->id = $qr['id'];
        $qrcode->data_id = $qr['id'];
        $qrcode->type = $qr['type'];
        $qrcode->ticket = $qr['ticket'];
        $qrcode->img_url = $qr['img_url'];
        $qrcode->img_link = $qr['img_link'];
        $qrcode->img_url_local = $qr['img_url_local'];
        $qrcode->add_time = $qr['add_time'];
        $qrcode->end_time = $qr['end_time'];
        $qrcode->save();
    }
    
    public static function createTplMsg($openid, $tpl_id, $content, $url, $topcolor){
        $msg_post = new WxPost();
        $msg_post->openid = $openid;
        $msg_post->tpl_id = $tpl_id;
        $msg_post->content = $content;
        $msg_post->url = $url;
        $msg_post->topcolor = $topcolor;
        $msg_post->status = WxPost::MSG_POST_WAIT;
        $msg_post->add_time = time();
        $msg_post->save();
        return $msg_post->id;
    }
    
    public static function updateTpls($tpl_list){
        WxTpl::deleteAll();
        foreach ($tpl_list as $tpl){
            $wx_tpl = new WxTpl();
            $wx_tpl->tpl_id = $tpl['template_id'];
            $wx_tpl->title = $tpl['title'];
            $wx_tpl->primary_industry = $tpl['primary_industry'];
            $wx_tpl->deputy_industry = $tpl['deputy_industry'];
            $wx_tpl->content = $tpl['content'];
            $wx_tpl->example = $tpl['example'];
            $wx_tpl->save();
        }
    }
    
    public static function getTpl($tpl_id){
        $tpl = WxTpl::findOne(['tpl_id'=>$tpl_id]);
        if($tpl){
            $tpl = $tpl->attributes;
        }
        return $tpl;
    }
    
    public static function getWxMsg(){
        $list = [];
        $msg_list = WxPost::findAll(['status' => WxPost::MSG_POST_WAIT]);
        foreach ($msg_list as $v) {
            $v['status'] = WxPost::MSG_POST_SENDING;
            $v->save();
            $list[] = $v->toArray();
        }
        return $list;
    }
    
    public static function updateMsgStatus($id, $is_send){
        $status = WxPost::MSG_POST_WAIT;
        if ($is_send){
            $status = WxPost::MSG_POST_FINISH;
        }
        return WxPost::updateAll(['status' => $status], "id = '$id'");
    }
    
    public static function getUserByID($uid){
        $info = $wx = WxUser::findOne(['id' => $uid]);
        if($wx){
            $info = $wx->toArray();
        }
        return $info;
    }
}



