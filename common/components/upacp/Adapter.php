<?php
namespace common\components\upacp;

use yii\base\Component;
use common\components\upacp\sdk\AcpService;

class Adapter extends Component
{
    public $merId;
    

    public function frontConsume($oid, $amount)
    {
        $params = [                    
            // TODO 以下信息需要填写
            'merId' => $this->merId, // 商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => $oid, // 商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => date('YmdHms',time()),//'20160303110352', // 订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt' => $amount,// 交易金额，单位分，此处默认取demo演示页面传递的参数
            //'reqReserved' =>'透传信息', //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据
        ];
                                      
                                      
        // TODO 其他特殊用法请查看 special_use_purchase.php
        
        
        AcpService::sign($params);
        $uri = SDK_FRONT_TRANS_URL;
        $html_form = AcpService::createAutoFormHtml($params, $uri);
        return $html_form;
    }
}