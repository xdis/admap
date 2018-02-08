<?php
namespace common\components\api\module;

use common\models\media\Media;
use common\models\media\MediaWx;
use common\models\user\UserCompete;
use common\models\order\Advertisement;

class MediaApi extends BaseAdapter
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => ApiFilter::className(),
                'rules' => [
                    [
                        'actions' => [
                            'get_media_list' => [
                                'type' => false,
                                'status' => false,
                                'uid' => false,
                                'cate' => false,
                                'fans' => false,
                                'read' => false,
                                'areaid' => false,
                                'key' => false,
                                'lvl' => false,
                                'region' => false,
                                'order' => false,
                                'p' => false,
                                'size' => false
                            ],
                            'get_media_cate' => [
                                'size' => false,
                                'type' => false
                            ],
                            'get_fans_read' => [
                                'size' => false,
                                'type' => false
                            ],
                            'get_media_index_list' => [
                                'size' => false,
                                'order' => false
                            ]
                        ]
                    ],
                    [
                        'roles' => [
                            '@'
                        ],
                        'actions' => [
                            'create_media' => [
                                'mid' => false,
                                'account' => false,
                                'type' => false,
                                'prices' => false
                            ],
                            'get_media' => [
                                'mid' => true
                            ],
                            'update_media' => [
                                'mid' => true
                            ],
                            'get_media_wx' => [
                                'account' => true
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function actionGet_media_list()
    {
        $code = self::CODE_SUCCESS;
        $data = Media::getMediaList($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionCreate_media()
    {
        $code = self::CODE_SUCCESS;
        $data = Media::createMedia($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionGet_media()
    {
        $code = self::CODE_SUCCESS;
        $data = Media::getMedia($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionUpdate_media()
    {
        $code = self::CODE_SUCCESS;
        $data = Media::updateMediaStatus($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionGet_media_wx()
    {
        $code = self::CODE_SUCCESS;
        $data = null;
        $account = $_POST['account'];
        $media_wx = MediaWx::findOne([
            'account' => $account
        ]);
        if ($media_wx) {
            $data = $media_wx->getInfo();
        } elseif (MediaWx::updateMediaData($account)) {
            $data = MediaWx::findOne([
                'account' => $account
            ])->getInfo();
        } else {
            $code = self::CODE_ERROR_UNKNOW;
            $data = 'Media Not Found!';
        }
        return [
            $code,
            $data
        ];
    }

    public function actionGet_media_cate()
    {
        $code = self::CODE_SUCCESS;
        $data = Media::getMediaCate($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionGet_fans_read()
    {
        $code = self::CODE_SUCCESS;
        $data = Media::getLvlType($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionGet_media_index_list()
    {
        $code = self::CODE_SUCCESS;
        $data = Media::getMediaIndexList($_POST);
        return [
            $code,
            $data
        ];
    }
}