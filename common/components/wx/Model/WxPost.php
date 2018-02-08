<?php

namespace common\components\wx\Model;

use Yii;

/**
 * This is the model class for table "{{%wx_msg_post}}".
 *
 * @property string $id
 * @property string $openid
 * @property string $tpl_id
 * @property string $content
 * @property string $url
 * @property string $topcolor
 * @property integer $status
 * @property integer $add_time
 * @property integer $post_time
 */
class WxPost extends \yii\db\ActiveRecord
{
    const MSG_POST_DRAFT = 0;//草稿
    const MSG_POST_WAIT = 1;//待发送
    const MSG_POST_SENDING = 2;//发送中
    const MSG_POST_FINISH =3;//已发送
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wx_msg_post}}';
    }

}
