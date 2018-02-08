<?php
namespace common\components\api\module;

use common\models\user\User;

class UserApi extends BaseAdapter
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => ApiFilter::className(),
                'rules' => [
                    [
                        'actions' => [
                            'login' => [
                                'username' => true,
                                'password' => true
                            ],
                            'check' => [
                                'valid' => true,
                                'value' => true
                            ],
                            'get_valid_code' => [
                                'mobile' => true,
                                'type' => true
                            ],
                            'check_valid_code' => [
                                'valid_code' => true
                            ],
                            'repassword' => [
                                'condition' => true,
                                'password' => true,
                                'random' => false
                            ],
                            'valid_permission' => [
                                'mobile' => true,
                                'valid_code' => true
                            ]
                        ]
                    ],
                    [
                        'roles' => [
                            '@'
                        ],
                        'actions' => [
                            'get_user_list' => [
                                'p' => false,
                                'size' => false
                            ],
                            'get_user_info' => [],
                            'logout' => [],
                            'get_user_info_number' => [],
                            'get_user_base_order' => [],
                            'update_user_infomation' => [
                                'resend' => false,
                                'nickname' => false,
                                'mobile' => false,
                                'email' => false,
                                'qq' => false,
                                'head_img' => false
                            ],
                            'update_message_set' => [
                                'need_mail' => false,
                                'need_phone' => false
                            ],
                            'valid_mail' => [
                                'email' => false
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function actionGet_user_info()
    {
        $code = self::CODE_SUCCESS;
        $data = User::getCurInfo();
        return [
            $code,
            $data
        ];
    }

    public function actionCheck()
    {
        $code = self::CODE_SUCCESS;
        $data = User::isExist($_POST) ? 1 : 0;
        return [
            $code,
            $data
        ];
    }

    public function actionLogin()
    {
        $code = self::CODE_SUCCESS;
        $data = User::login($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionGet_user_info_number()
    {
        $code = self::CODE_SUCCESS;
        $data = User::getUserInfoNumber($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionGet_user_base_order()
    {
        $code = self::CODE_SUCCESS;
        $data = User::getUserBaseOrder($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionGet_user_list()
    {
        $code = self::CODE_SUCCESS;
        $data = User::getUserList($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionRepassword()
    {
        $code = self::CODE_SUCCESS;
        $data = User::reUserPassword($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionLogout()
    {
        $code = self::CODE_SUCCESS;
        $data = User::logout();
        return [
            $code,
            $data
        ];
    }

    public function actionGet_valid_code()
    {
        $code = self::CODE_SUCCESS;
        $data = User::getValidCode($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionCheck_valid_code()
    {
        $code = self::CODE_SUCCESS;
        $data = User::checkValidCode($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionUpdate_user_infomation()
    {
        $code = self::CODE_SUCCESS;
        $data = User::updateUserMessage($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionUpdate_message_set()
    {
        $code = self::CODE_SUCCESS;
        $data = User::updateMessageSet($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionValid_mail()
    {
        $code = self::CODE_SUCCESS;
        $data = User::validMail($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionValid_permission()
    {
        $code = self::CODE_SUCCESS;
        $data = User::validPermission($_POST);
        return [
            $code,
            $data
        ];
    }
}