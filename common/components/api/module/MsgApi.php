<?php
namespace common\components\api\module;

use common\models\msg\MsgBox;

class MsgApi extends BaseAdapter
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => ApiFilter::className(),
                'rules' => [
                    [
                        'roles' => [
                            '@'
                        ],
                        'actions' => [
                            'get_msg_list' => [
                                'type' => false,
                                'msg_type' => false,
                                'p' => false,
                                'size' => false,
                                'status' => false
                            ],
                            'change_read_status' => [
                                'msg_id' => true,
                                'status' => false
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function actionGet_msg_list()
    {
        $code = self::CODE_SUCCESS;
        $data = MsgBox::getMsgList($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionChange_read_status()
    {
        $code = self::CODE_SUCCESS;
        $data = MsgBox::updateMsgStatus($_POST);
        return [
            $code,
            $data
        ];
    }
}