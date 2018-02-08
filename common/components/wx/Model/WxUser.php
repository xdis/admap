<?php

namespace common\components\wx\Model;

use Yii;

/**
 * This is the model class for table "{{%user_wx}}".
 *
 * @property string $id
 * @property string $open_id
 * @property string $params
 * @property string $nick_name
 * @property integer $sex
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $lang
 * @property string $head_img_url
 * @property integer $is_subscribe
 * @property integer $is_update
 * @property integer $sub_time
 * @property integer $quit_time
 * @property integer $add_time
 * @property integer $last_active_time
 * @property integer $last_update_time
 */
class WxUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_wx}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['open_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'open_id' => 'Open ID',
            'params' => 'Params',
            'nick_name' => 'Nick Name',
            'sex' => 'Sex',
            'country' => 'Country',
            'province' => 'Province',
            'city' => 'City',
            'lang' => 'Lang',
            'head_img_url' => 'Head Img Url',
            'is_subscribe' => 'Is Subscribe',
            'is_update' => 'Is Update',
            'sub_time' => 'Sub Time',
            'quit_time' => 'Quit Time',
            'add_time' => 'Add Time',
            'last_active_time' => 'Last Active Time',
            'last_update_time' => 'Last Update Time',
        ];
    }
}
