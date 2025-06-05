<?php

return [
    'id' => 'yii2-user-tests',
    'basePath' => dirname(__DIR__),
    'language' => 'en-US',
    'aliases' => [
        '@Da/User' => dirname(dirname(dirname(__DIR__))) . '/src/User',
        '@tests' => dirname(dirname(__DIR__)),
        '@vendor' => VENDOR_DIR,
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'bootstrap' => [
        'filex'
    ],
    'modules' => [
        'filex' => require __DIR__ . '/filex.php',
    ],
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'assetManager' => [
            'linkAssets' => true,
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager'
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\UserIdentity',
        ],
        'storage' => require __DIR__ . '/storage.php',
        'db' => require __DIR__ . '/db.php',
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
        ],
    ],
    'params' => [],
];
