<?php
namespace common\components\api\module;

use common\models\order\Advertisement;
use common\models\order\Order;
use common\models\user\User;
use common\models\user\UserCompete;

class OrderApi extends BaseAdapter
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => ApiFilter::className(),
                'rules' => [
                    [
                        'actions' => [
                            'get_order_list' => [
                                'status' => true,
                                'mid' => false,
                                'p' => false,
                                'size' => false,
                                'uid' => false,
                                'type' => false,
                                'choose_type' =>false,
                                'order' => false,
                                'is_compete'=>false,
                            ],
                            'get_hot_compete_list' => [
                                'size' => false,
                            ],
                            'get_order' => [
                                'oid' => true,
                            ],
                            'create_user_compete' => [
                                'mid'=> true,
                                'oid'=> true,
                                'm_publish_time'=> true,
                                'm_show_position'=> true,
                                'm_attachment'=> true,
                                'offer_price'=> true,
                                'm_enable_reads'=> true,
                                'm_arg_or_ip_price' => true,
                            ],
                        ]
                    ],
                    [
                        'roles' => [
                            '@'
                        ],
                        'actions' => [
                            'next_flow' => [
                                'oid' => true
                            ],
                            'reject_order' => [
                                'oid' => true,
                                'err_code' => true,
                                'err_child' => false,
                                'err_remark' => false
                            ],
                            'feedback' => [
                                'oid' => true,
                                'img' => true,
                                'push' => true,
                                'browse' => true,
                                'read' => true,
                                'like' => true,
                                'forward' => true,
                                'remark' => false
                            ],
                            'get_compete_list' => [
                                'p' => false,
                                'size' => false,
                                'status' => false,
                                'is_select' => false,
                                'order' =>false,
                            ],
                        ]
                    ],
                    [
                        'roles' => [
                            User::USER_ROLE_ADVERT
                        ],
                        'actions' => [
                            'create_ad' => [
                                'title' => true,
                                'attach' => true,
                                'href' => true,
                                'remark' => true,
                                'publish_time' => true
                            ],
                            'create_order' => [
                                'oid' => false,
                                'm_list' => true,
                                'is_invoice' => true,
                                'amount' => true,
                                'invoice' => false
                            ],
                            'update_ad' => [
                                'aid' => true,
                                'title' => false,
                                'attach' => false,
                                'href' => false,
                                'remark' => false,
                                'publish_time' => false
                            ],
                            'choose_media' => [
                                'oid' => true,
                                'mid' => false,
                                'select' => false,
                                'orderamount' =>false,
                                'invoice' => false,
                            ],
                            'create_ad_order' => [
                                'oid' => false,
                                'order_status' => true,
                                'ad_list' => [
                                    true,
                                    [
                                        'title' => false,
                                        'publish_time' => false,
                                        'publish_time_end' => false,
                                        'remark' => false,
                                        'href' => false,
                                        'choose_type' => false,
                                        'show_position' => false,
                                        'extend_list' => [
                                            true,
                                            [
                                                'arg_or_ip_price' => false,
                                                'push_cate' => false,
                                                'enable_reads' => false,
                                                'enable_fens' => false,
                                                'ad_amount' => false,
                                                'ad_invoice' => false,
                                            ]
                                        ],
                                        'ad_atta_list' => [
                                            true,
                                            [
                                                'atta_title' => false,
                                                'content' => false,
                                                'big_cover' => false,
                                                'small_cover' => false,
                                                'summary' => false,
                                                'author' => false,
                                                'is_show_cover' => false
                                            ]
                                        ],
                                        'ad_about_list' => [
                                            true,
                                            [
                                               'agestate' => false,
                                                'incomestate' => false,
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function actionCreate_ad()
    {
        $code = self::CODE_SUCCESS;
        $data = Advertisement::createAD($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionUpdate_ad()
    {
        $code = self::CODE_SUCCESS;
        $data = Advertisement::updateAD($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionCreate_order()
    {
        $code = self::CODE_SUCCESS;
        $data = Order::createOrder($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionGet_order_list()
    {
        $code = self::CODE_SUCCESS;
        $data = Order::getOrderList($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionGet_order()
    {
        $code = self::CODE_SUCCESS;
        $data = Order::getOrder($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionNext_flow()
    {
        $code = self::CODE_SUCCESS;
        $data = Order::nextFlow($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionReject_order()
    {
        $code = self::CODE_SUCCESS;
        $data = Order::reject($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionFeedback()
    {
        $code = self::CODE_SUCCESS;
        $data = Order::updateProfile($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionChoose_media()
    {
        $code = self::CODE_SUCCESS;
        $data = Order::chooseMedia($_POST);
        return [
            $code,
            $data
        ];
    }

    public function actionCreate_ad_order()
    {
        $code = self::CODE_SUCCESS;
        $data = Order::createAdOrder($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionGet_compete_list(){
        $code = self::CODE_SUCCESS;
        $data = Order::getCompeteList($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionGet_hot_compete_list(){
        $code = self::CODE_SUCCESS;
        $data = Order::getHotCompeteList($_POST);
        return [
            $code,
            $data
        ];
    }
    
    public function actionCreate_user_compete(){
        $code = self::CODE_SUCCESS;
        $data = UserCompete::createCompete($_POST);
        return [
            $code,
            $data
        ];
    }
   
}