<?php

namespace common\components\wx\Model;

use Yii;

/**
 * This is the model class for table "{{%wx_tpl}}".
 *
 * @property string $tpl_id
 * @property string $title
 * @property string $content
 * @property string $primary_industry
 * @property string $deputy_industry
 * @property string $example
 * 
 */
class WxTpl extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wx_tpl}}';
    }
}
