<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                // send all mails to a file by default.
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'xaussr7YZoTWpQ3umjGBkE18OApllTb8',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // ===== 患者端小程序 API（wechat_patient 模块） =====
                'POST wechat-patient/login' => 'wechat_patient/login/index',
                'GET wechat-patient/profile' => 'wechat_patient/profile/index',
                'POST wechat-patient/profile' => 'wechat_patient/profile/save',
            ],
        ],

    ],
    'modules' => [
        // 患者端小程序 API 模块
        'wechat_patient' => [
            'class' => \app\wechat_patient\Module::class,
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        // 与 debug 保持一致：允许本机+局域网访问
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.*'],
        'generators' => [
            'model' => [
                'class' => \yii\gii\generators\model\Generator::class,
                // Gii 生成模型的默认命名空间（→ models/base 目录）；
                // 手写业务子类放在 app\models，继承 base 类
                'ns' => 'app\models\base',
            ],
        ],
    ];
}

return $config;
