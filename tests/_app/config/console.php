<?php

return [
    'id' => 'yii2-user-tests-console',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@Da/User' => dirname(dirname(dirname(__DIR__))) . '/src/User',
        '@tests' => dirname(dirname(__DIR__)),
        '@vendor' => VENDOR_DIR,
    ],
    'components' => [
        'db' => require __DIR__ . '/db.php',
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                __DIR__ . '/../../../src/migrations',
                '@yii/rbac/migrations'
            ],
        ],
    ]
];
