<?php
namespace backend\controllers;

use yii;

class WxsrvController extends yii\web\Controller
{
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
            ]
        ];
    }
    
    public function actionIndex()
    {
        Yii::$app->wx->responseMsg();
    }
    
}