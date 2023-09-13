<?php

$dbDsn = $_ENV['DB_DSN'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASS'];
$db = [
    'class' => 'yii\db\Connection',
    'dsn' => $dbDsn,
    'username' => $dbUser,
    'password' => $dbPassword,
    'charset' => 'utf8',
];

return $db;
