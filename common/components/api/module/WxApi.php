<?php
namespace common\components\api\module;

class WxApi extends BaseAdapter
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
                            'upload_image' => [
                                'type' => false,
                                'file_path' => false
                            ],
                            'upload_longimage' => [
                                'type' => false,
                                'file_path' => false
                            ],
                            'get_longimage' => [
                            ],
                            'upload_textimage' => [
                                'title' => false,
                                'thumb_media_id' => false,
                                'author' => false,
                                'digest' => false,
                                'show_cover_pic' => false,
                                'content' => false,
                                'content_source_url' => false,
                            ],
                            'mass_openid_data'=>[
                                'touser' => false,
                                'mpnews' => false,
                                'msgtype' => false,
                            ],
                            'mass_all_data' => [
                                'touser' => false,
                                'mpnews' => false,
                                'msgtype' => false,
                                'title' => false,
                                'description' => false,
                                'thumb_media_id' => false,
                            ],
                            'get_user_list' => [
                                'next_openid' => false,
                            ],
                            'get_material_list' => [
                                'type'=> false,
                                'offset'=> false,
                                'count'=> false,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    //上传临时文件
    public function actionUpload_image()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->uploadMedia($_POST);
        return [
            $code,
            $data
        ];
    }
    
    //上传永久文件
    public function actionUpload_longimage()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->uploadMediaLong($_POST);
        return [
            $code,
            $data
        ];
    }
    
    //上传永久图文素材
    public function actionUpload_textimage()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->uploadTextImage($_POST);
        return [
            $code,
            $data
        ];
    }
    
    //获取永久素材
    public function actionGet_longimage()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->getMediaLong($_POST);
        return [
            $code,
            $data
        ];
    }
    
    //根据OpenID列表群发 (订阅号不可用, 服务号认证后可用)
    public function actionMass_openid_data()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->massOpenIdData($_POST);
        return [
            $code,
            $data
        ];
    }
    
    //根据分组进行群发 (订阅号与服务号认证后均可用)
    public function actionMass_all_data()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->massAll($_POST);
        return [
            $code,
            $data
        ];
    }
    
    //获取关注者列表
    public function actionGet_user_list()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->getUserList($_POST);
        return [
            $code,
            $data
        ];
    }
    
    //获取素材列表
    public function actionGet_material_list()
    {
        $code = self::CODE_SUCCESS;
        $data = \Yii::$app->wx->getMaterialList($_POST);
        return [
            $code,
            $data
        ];
    }
}