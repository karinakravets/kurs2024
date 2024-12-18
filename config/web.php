<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name'=>'shop',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '123',
            'parsers' => [
            'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'POST register' => 'user/create',//+
                'POST login' => 'user/login',//+
                'GET user' => 'user/one',//+
                'POST user/update' => 'user/upgrade',//+

                'GET dish' => 'dish/all',//+
                'GET dish/<id:\d+>' => 'dish/one',//+
                'POST admin/dish' => 'dish/create',//+
                'POST admin/dish/<id:\d+>' => 'dish/upgrade',//+
                'DELETE admin/dish/<id:\d+>'=>'dish/delete',//+
                
                'POST cart' => 'cart/add',//+
                'GET cart' => 'cart/get-cart',//+
                'DELETE cart/one/<id:\d+>' => 'cart/remove-cart',//+

                'POST orders' => 'dish/create-orders',//+
                'GET orders' => 'dish/get-orders', //+
                'PATCH admin/orders/<id:\d+>' => 'dish/update-order-status',//+
                
                
                
                
              
        ],
    ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return $config;