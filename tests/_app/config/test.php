<?php

$config = require __DIR__ . '/main.php';

// Test-specific configuration
$config['id'] = 'yii2-files-catalog-tests';
$config['components']['assetManager']['basePath'] = __DIR__ . '/../web/assets';
$config['components']['urlManager']['showScriptName'] = true;
$config['components']['request']['cookieValidationKey'] = 'test';
$config['components']['request']['enableCsrfValidation'] = false;


// Disable cache for testing
$config['components']['cache'] = [
    'class' => 'yii\caching\DummyCache',
];

// Configure test-specific settings for the module
$config['modules']['filex']['enableACL'] = false;

return $config;
