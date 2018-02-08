<?php

namespace common\components\wx\Model;

use Yii;

/**
 * This is the model class for table "{{%wx_qrcode}}".
 *
 * @property string $id
 * @property string $data_id
 * @property integer $type
 * @property string $ticket
 * @property string $img_url
 * @property string $img_link
 * @property string $img_url_local
 * @property string $add_time
 * @property string $end_time
 */
class WxQRCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wx_qrcode}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'add_time', 'end_time'], 'integer'],
            [['data_id'], 'string', 'max' => 100],
            [['ticket'], 'string', 'max' => 255],
            [['img_url', 'img_link', 'img_url_local'], 'string', 'max' => 512]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_id' => 'Data ID',
            'type' => 'Type',
            'ticket' => 'Ticket',
            'img_url' => 'Img Url',
            'img_link' => 'Img Link',
            'img_url_local' => 'Img Url Local',
            'add_time' => 'Add Time',
            'end_time' => 'End Time',
        ];
    }
}
