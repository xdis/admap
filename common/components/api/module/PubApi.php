<?php
namespace common\components\api\module;


use common\models\user\User;
use common\models\pub\WechatPost;
use common\models\pub\Banner;
class PubApi extends BaseAdapter
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => ApiFilter::className(),
                'rules' => [
                    [
                        'actions' => [
                            'get_wechat_post_list' =>[
                                'size' => false
                            ],
                            'get_wechat_post' => [
                            ],
                            'get_banner_list' =>[
                                'size' => false
                            ],
                            'test_get_openid' =>[
                                
                            ],
                        ],
                    ],
                    [
                        'roles' => [
                            '@'
                        ],
                        'actions' => [
                        ]
                        
                    ],
                      [
                        'roles' => [
                            User::USER_ROLE_ADVERT
                        ],
                        'actions' => [
                            'add_wechat_post' => [
                                'post_title'=>false,
                                'post_content'=>false,
                                'post_author'=>false,
                                'post_readnum'=>false,
                                'post_likenum'=>false,
                                'post_img'=>false,
                                'post_remark'=>false,
                                'post_release_time'=>false,
                                'href' => false,
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
    
    public function actionGet_wechat_post_list()
    {
        $code = self::CODE_SUCCESS;
        $data = WechatPost::getWechatPostList($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionGet_wechat_post()
    {
        $code = self::CODE_SUCCESS;
        $data = WechatPost::getWechatPost($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionAdd_wechat_post()
    {
        $code = self::CODE_SUCCESS;
        $data = WechatPost::addWechatPost($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionGet_banner_list()
    {
        $code = self::CODE_SUCCESS;
        $data = Banner::getBannerList($_POST);
        return [
            $code,
            $data
        ];
    }
    public function actionTest_get_openid(){
        \Yii::$app->wx->getUserOpenID();
    }
}