<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
require 'vendor/autoload.php';
$config = array_merge(
    require 'common/config/main.php',
    require 'common/config/main-local.php',
    require 'frontend/config/main.php'
);
$app = new yii\web\Application($config);

echo "=== Testing Manager Login ===\n";
$user = \common\models\User::findByUsername('manager');
echo 'User found: ' . ($user ? $user->email : 'NO') . "\n";
if ($user) {
    echo 'ID: ' . $user->id . "\n";
    echo 'Status: ' . $user->status . "\n";
    echo 'Password validation: ' . ($user->validatePassword('manager123') ? 'YES' : 'NO') . "\n";
}

echo "\n=== Testing Admin Login ===\n";
$admin = \common\models\User::findByUsername('admin');
echo 'User found: ' . ($admin ? $admin->email : 'NO') . "\n";
if ($admin) {
    echo 'ID: ' . $admin->id . "\n";
    echo 'Status: ' . $admin->status . "\n";
    echo 'Password validation: ' . ($admin->validatePassword('admin') ? 'YES' : 'NO') . "\n";
}

echo "\n=== Testing Driver1 Login ===\n";
$driver = \common\models\User::findByUsername('driver1');
echo 'User found: ' . ($driver ? $driver->email : 'NO') . "\n";
if ($driver) {
    echo 'ID: ' . $driver->id . "\n";
    echo 'Status: ' . $driver->status . "\n";
    echo 'Password validation: ' . ($driver->validatePassword('driver123') ? 'YES' : 'NO') . "\n";
}
