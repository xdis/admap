<?php
namespace common\components\api;

use common\components\api\module\BaseAdapter;
use common\components\api\module\PubApi;
use common\components\api\module\UserApi;
use common\components\api\module\WxApi;
use common\components\api\module\OrderApi;
use common\components\api\module\MsgApi;
use common\components\api\module\MediaApi;
use common\components\api\module\AccountApi;
use common\components\api\module\ContentApi;

class Adapter extends BaseAdapter
{

    const MODULE_PUB = 'PUB';
    const MODULE_USER = 'USER';
    const MODULE_WX = 'WX';
    const MODULE_ORDER = 'ORDER';
    const MODULE_MSG = 'MSG';
    const MODULE_MEDIA = 'MEDIA';
    const MODULE_ACCOUNT = 'ACCOUNT';
    const MODULE_CONTENT = 'CONTENT';

    public $auth = [];

    public function actions()
    {
        return [
            self::MODULE_PUB => [
                'class' => PubApi::className()
            ],
            self::MODULE_USER => [
                'class' => UserApi::className()
            ],
            self::MODULE_WX => [
                'class' => WxApi::className()
            ],
            self::MODULE_ORDER => [
                'class' => OrderApi::className()
            ],
            self::MODULE_MSG => [
                'class' => MsgApi::className()
            ],
            self::MODULE_MEDIA => [
                'class' => MediaApi::className()
            ],
            self::MODULE_ACCOUNT => [
                'class' => AccountApi::className()
            ],
            self::MODULE_CONTENT => [
                'class' => ContentApi::className()
            ],
        ];
    }

    public function handle($path)
    {
        header('Access-Control-Allow-Origin: *');
        $ret = self::getCodeArray(self::CODE_ERROR_UNKNOW);
        if ($path) {
            $args = explode('/', $path);
            if (sizeof($args) > 1 && isset($this->auth[$args[0]])) {
                $ret = $this->runAction(strtoupper($args[0]), $args[1]);
            } else {
                $ret = self::getCodeArray(self::CODE_ERROR_AUTH);
            }
        }
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
    }
}