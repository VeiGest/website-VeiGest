<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// Configurar aplicação
$config = require __DIR__ . '/console/config/main.php';
if (file_exists(__DIR__ . '/console/config/main-local.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/console/config/main-local.php');
}
$commonConfig = require __DIR__ . '/common/config/main.php';
if (file_exists(__DIR__ . '/common/config/main-local.php')) {
    $commonConfig = yii\helpers\ArrayHelper::merge($commonConfig, require __DIR__ . '/common/config/main-local.php');
}
$config = yii\helpers\ArrayHelper::merge($commonConfig, $config);
new \yii\console\Application($config);

echo "🔧 Gerando novos hashes de senha...\n\n";

// Lista de senhas para atualizar
$passwords = [
    'admin123',
    'gestor123', 
    'driver123'
];

foreach ($passwords as $password) {
    $hash = Yii::$app->security->generatePasswordHash($password);
    echo "Senha: $password\n";
    echo "Hash: $hash\n\n";
    
    // Testar se o hash funciona
    if (Yii::$app->security->validatePassword($password, $hash)) {
        echo "✓ Hash válido para $password!\n\n";
    } else {
        echo "✗ Hash inválido para $password!\n\n";
    }
}
