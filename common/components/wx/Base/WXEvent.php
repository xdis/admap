<?php
namespace common\components\wx\Base;

use yii\base\Object;
class WXEvent extends Object
{

    const WX_ACCESS_TOKEN = "WX_ACCESS_TOKEN";
    const WX_JSAPI_TICKET = "WX_JSAPI_TICKET";
    const QR_SCENE = "QR_SCENE";
    const QR_LIMIT_SCENE = "QR_LIMIT_SCENE";

    public $eventMap = array();

    function listenTo($model, $e_name, $callback, $context = null)
    {
        if (! $context) {
            $context = $this;
        }
        $model->eventMap[$e_name] = array(
            $context,
            $callback
        );
    }

    function listenArray($model, $event_lst, $context = null)
    {
        if (! $context) {
            $context = $this;
        }
        foreach ($event_lst as $key => $value) {
            $this->listenTo($model, $key, $value, $context);
        }
    }

    public function trigger($e_name, $scope = array())
    {
        call_user_func_array($this->eventMap[$e_name], $scope);
    }
}
?>