<?php

namespace common\components\wx\Model;

use Yii;

/**
 * This is the model class for table "{{%wx_config}}".
 *
 * @property string $appid
 * @property string $item_key
 * @property string $item_value
 * @property integer $last_update_time
 */
class WxConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wx_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['appid', 'item_key'], 'required'],
            [['last_update_time'], 'integer'],
            [['appid'], 'string', 'max' => 100],
            [['item_key', 'item_value'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'appid' => 'Appid',
            'item_key' => 'Item Key',
            'item_value' => 'Item Value',
            'last_update_time' => 'Last Update Time',
        ];
    }
}
