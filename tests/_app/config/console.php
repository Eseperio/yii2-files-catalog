<?php

return [
    'id' => 'yii2-test-console',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@tests' => dirname(dirname(__DIR__)),
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'log' => null,
        'cache' => null,
        'db' => require __DIR__ . '/db.php',
        'authManager'=>[
            'class'=> 'yii\rbac\PhpManager',
        ]
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                __DIR__ . '/../../../src/migrations',
                '@yii/rbac/migrations'
            ],
        ],
    ],
];
