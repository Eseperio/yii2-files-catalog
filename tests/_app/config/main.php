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
    'bootstrap' => [],
    'modules' => [
        'filex' => [
            'class' => \eseperio\filescatalog\FilesCatalogModule::class,
            'identityClass' => 'app\models\UserIdentity',
            'salt' => 'test'
        ],
    ],
    'components' => [
        'authManager'=>[
          'class'=> 'yii\rbac\PhpManager'
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\UserIdentity',
        ],
        'storage'=>[
            'class' => 'creocoder\flysystem\LocalFilesystem',
            'path' => __DIR__.'/../uploads'
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../assets',
        ],
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
