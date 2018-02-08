<?php
namespace common\components\wx\Base;

interface WXLogicInterface
{   
    public function callback_text($postObj);   
    public function callback_image($postObj);
    public function callback_voice($postObj);
    public function callback_video($postObj);
    public function callback_shortvideo($postObj);
    public function callback_location($postObj);
    public function callback_subscribe($postObj);
    public function callback_unsubscribe($postObj);
    public function callback_scan($postObj);
    public function callback_e_location($postObj);
    public function callback_click($postObj);
    public function callback_view($postObj);
}

?>