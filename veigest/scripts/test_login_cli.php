<?php
define('YII_DEBUG', true);
define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';
require __DIR__ . '/../frontend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../frontend/config/main.php',
    require __DIR__ . '/../frontend/config/main-local.php'
);

new yii\web\Application($config);

use common\models\User;

function test($input, $password) {
    echo "\nTesting login for '{$input}' ...\n";
    $user = User::findByUsername($input);
    if (!$user) {
        echo " - Not found by username, trying email...\n";
        $user = User::findByEmail($input);
    }
    if (!$user) { echo " ❌ User not found\n"; return; }
    echo " - Found user id={$user->id}, username={$user->username}, nome={$user->nome}, role={$user->role}\n";
    echo User::findIdentity($user->id) ? " - Identity is active (estado=ativo)\n" : " - Identity inactive\n";
    $ok = $user->validatePassword($password);
    echo $ok ? " ✓ Password valid\n" : " ❌ Invalid password\n";
}

test('gestor', 'gestor123');
test('gestor@veigest.com', 'gestor123');
test('admin', 'admin123');
test('admin@veigest.com', 'admin123');

echo "\nDone.\n";
