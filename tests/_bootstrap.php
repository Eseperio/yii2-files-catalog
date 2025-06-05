<?php


defined('YII_DEBUG') or define('YII_DEBUG', $_ENV['YII_DEBUG'] ?? true);
defined('YII_ENV') or define('YII_ENV', $_ENV['YII_ENV'] ?? 'test');


// Search for autoload, since performance is irrelevant and usability isn't!
$dir = __DIR__ . '/..';
while (!file_exists($dir . '/vendor/autoload.php')) {
    if ($dir == dirname($dir)) {
        throw new \Exception('Failed to locate autoload.php');
    }
    $dir = dirname($dir);
}

$vendor = $dir . '/vendor';

define('VENDOR_DIR', $vendor);


require_once $vendor . '/autoload.php';
require $vendor . '/yiisoft/yii2/Yii.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required([
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASS',
    'DB_CHARSET',
]);
