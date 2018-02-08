<?php
namespace common\components\api\module;

use yii\base\Module;
use common\base\BaseController;

class BaseAdapter extends BaseController
{
    public $enableCsrfValidation = false;
    
    const CODE_ERROR_UNKNOW = 0;
    const CODE_SUCCESS = 1;
    const CODE_ERROR_AUTH = 900;
    const CODE_ERROR_NOT_ALLOW = 901;
    const CODE_ERROR_UNKNOW_API = 902;
    
    public function __construct($id = 'api', $module = null, $config = [])
    {
        $module = new Module($id);
        parent::__construct($id, $module, $config);
    }
    
    public function runWithParams($arg){
        return $this->parseApi($arg);
    }
    
    public function parseApi($arg){
        $result = [];
        $action = $this->createAction($arg);
        if ($action !== null) {
            if ($this->beforeAction($action)) {
                list($result['code'], $result['data']) = $action->runWithParams([]);
                $result = $this->afterAction($action, $result);
            }else{
                $result = self::getCodeArray(self::CODE_ERROR_NOT_ALLOW);
            }
        }else{
            $result = self::getCodeArray(self::CODE_ERROR_UNKNOW_API);
        }
        return $result;
    }
    
    public static function getCodeArray($code){
        switch ($code){
            case self::CODE_SUCCESS:
                $msg = 'Success';
                break;
            case self::CODE_ERROR_AUTH:
                $msg = '访问受限';
                break;
            case self::CODE_ERROR_NOT_ALLOW:
                $msg = '无权操作';
                break;
            case self::CODE_ERROR_UNKNOW_API:
                $msg = '未知API';
                break;
            case self::CODE_ERROR_UNKNOW:
            default:
                $msg = '未知错误';
                break;
        }
        return [
            'code' => $code,
            'data' => $msg
        ];
    }
}