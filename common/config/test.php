<?php
return [
    'id' => 'app-common-tests',
    'basePath' => dirname(__DIR__),
    'components' => [
        'user' => [
		//添加测试文件ee rrrrr   测试 3444     55555 778    55555
            'class' => 'yii\web\User',
            'identityClass' => 'common\models\User',
        ],
    ],
];
