<?php
namespace common\components\alidayu;

use yii\base\Component;
use common\models\user\UserConfirm;
use common\helper\Logger;
use common\models\pub\Conf;

require_once 'sdk/TopSdk.php';

class Adapter extends Component
{
    public $appkey = '';
    public $secretKey = '';
    private $_client = null;
    private $_logger = null;
    
    public function init(){
        $this->_client = new \TopClient($this->appkey, $this->secretKey);
        $this->_client->format = "json";
        $this->_logger = Logger::getLogger();
    }
    
    public function sendMsg($mobile, $msg_type)
    {
        $this->_logger->debug("[ALIDAYU] Send MSG to $mobile ...");
        
        $valid_code = substr(str_shuffle('1234567890'), 0, 6);
        //$title = Conf::getSiteTitle();
        $title = 'title';
        $str = "{project:'$title',code:'$valid_code'}";
        $this->_logger->debug("[ALIDAYU] Result = ".json_encode($str, JSON_UNESCAPED_UNICODE));
        $req = new \AlibabaAliqinFcSmsNumSendRequest();
        $req->setSmsType("normal");
        switch ($msg_type) {
            case UserConfirm::CONFIRM_EMAIL:
                $req->setSmsFreeSignName("注册验证");
                $req->setSmsTemplateCode("SMS_36120144");
                break;
            case UserConfirm::RESET_PASSWORD:
                $req->setSmsFreeSignName("注册验证");
                $req->setSmsTemplateCode("SMS_36000242");
                break;
        }
        $req->setSmsParam($str);
        $req->setRecNum($mobile);
        $result = $this->_client->execute($req);
        
        $this->_logger->debug("[ALIDAYU] Result = ".json_encode($result, JSON_UNESCAPED_UNICODE));
        
        $ret = false;
        if (isset($result['result']) && $result['result']['err_code'] == '0') {
            \Yii::$app->session['alidayu_valid_mobile'] = $mobile;
            \Yii::$app->session['alidayu_valid_code'] = $valid_code;
            $ret = true;
        }
        return $ret;
    }
    
    public function checkValidCode($valid_code, $mobile = null){
        $ret = false;
        if(isset(\Yii::$app->session['alidayu_valid_code']) && \Yii::$app->session['alidayu_valid_code'] == $valid_code){
            if(!$mobile || \Yii::$app->session['alidayu_valid_mobile'] == $mobile){
                $ret = true;
            }
        }
        return $ret;
    }
}