<?php
return [
    'id' => 'app-common-tests',
    'basePath' => dirname(__DIR__),
    'components' => [
        'user' => [
    //开始测试的  从dev-test推代码到develop
	//提交代码12345
            'class' => 'yii\web\User',
            'identityClass' => 'common\models\User',
        ],
    ],
];
