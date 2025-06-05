<?php


require dirname(__DIR__) . '/../_bootstrap.php';



$config = require __DIR__ . '/../config/main.php';
// enable link assets for testing
$config['components']['assetManager']['linkAssets'] = true;

(new yii\web\Application($config))->run();
