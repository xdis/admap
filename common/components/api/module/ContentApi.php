<?php
namespace common\components\api\module;

use common\models\content\ContentWx;
class ContentApi extends BaseAdapter
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
                            'create_content_wx' => [
                                'content_id' => false,
                                'title' => false,
                                'content' => false,
                                'type' => false,
                                'author' => false,
                                'picture' => false,
                                'isshow' => false,
                                'abstract' => false,
                                'href' => false,
                                'status' => false,
                            ],
                            'get_content_wx' => [
                                'content_id' => false,
                                'size' => false,
                                'p' => false,
                                'status' => false,
                                'key' => false,
                                'type' => false,
                            ],
                            'delete_content_wx' => [
                                'content_id' => true,
                            ],
                            'change_content_wx_status' => [
                                'content_id' => true,
                                'status' => false
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    public function actionCreate_content_wx()
    {
        $code = self::CODE_SUCCESS;
        $data = ContentWx::Createcontentwx($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionGet_content_wx()
    {
        $code = self::CODE_SUCCESS;
        $data = ContentWx::getContentWx($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionDelete_content_wx()
    {
        $code = self::CODE_SUCCESS;
        $data = ContentWx::DeleteContentWx($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionChange_content_wx_status()
    {
        $code = self::CODE_SUCCESS;
        $data = ContentWx::updateContentWxStatus($_POST);
        return [
            $code,
            $data
        ];
    }
}