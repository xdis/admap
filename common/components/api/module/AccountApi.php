<?php
namespace common\components\api\module;

use common\models\account\Account;
class AccountApi extends BaseAdapter
{
    public function behaviors(){
        return [
            'access' => [
                'class' => ApiFilter::className(),
                'rules' => [
                    [
                        'actions' => [
                        ],
                    ],
                    [
                        'roles' => [
                            '@'
                        ],
                        'actions' => [
                            'get_account_list' => [
                                'p' => false,
                                'size' => false
                            ],
                            'upload_image' => [
                                'type' => false,
                                'file_path' => false
                            ],
                            'upload_longimage' => [
                            ],
                            'get_longimage' => [
                            ],
                            'upload_textimage' => [
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    public function actionGet_account_list()
    {
        $code = self::CODE_SUCCESS;
        $data = Account::getAccountList($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionUpload_image()
    {
        $code = self::CODE_SUCCESS;
        $type = '';
        $file_path = '';
        $data = \Yii::$app->wx->uploadMedia($type,$file_path);
        return [
            $code,
            $data
        ];
    }
    
    public function actionUpload_longimage()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->uploadMediaLong();
        return [
            $code,
            $data
        ];
    }
    
    public function actionUpload_textimage()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->uploadTextImage();
        return [
            $code,
            $data
        ];
    }
    
    public function actionGet_longimage()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->getMediaLong();
        return [
            $code,
            $data
        ];
    }
}