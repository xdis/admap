<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<controller:site>/<action:(index|api)>/<path:.*>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<p:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<status:\w+>/<id:.*>' => '<controller>/<action>',
            ]
        ],
    ],
];
