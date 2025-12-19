<?php
// Script para testar validação de senha
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// Configurar aplicação console mínima
$config = require __DIR__ . '/console/config/main.php';
$commonConfig = require __DIR__ . '/common/config/main.php';

if (file_exists(__DIR__ . '/console/config/main-local.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/console/config/main-local.php');
}

if (file_exists(__DIR__ . '/common/config/main-local.php')) {
    $commonConfig = yii\helpers\ArrayHelper::merge($commonConfig, require __DIR__ . '/common/config/main-local.php');
}

$config = yii\helpers\ArrayHelper::merge($commonConfig, $config);
$app = new \yii\console\Application($config);

echo "🔐 Testando validação de senha...\n\n";

// Buscar usuário admin
$user = \common\models\User::find()->where(['username' => 'admin'])->one();

if (!$user) {
    echo "❌ Usuário admin não encontrado\n";
    exit(1);
}

echo "✅ Usuário encontrado: {$user->username}\n";
echo "📊 Status: {$user->status} / Estado: {$user->estado}\n";
echo "🔑 Hash: " . substr($user->password_hash, 0, 30) . "...\n\n";

// Testar diferentes senhas
$testPasswords = ['admin', 'admin123', '123456'];

foreach ($testPasswords as $testPassword) {
    echo "🧪 Testando senha: '$testPassword'\n";
    
    if ($user->validatePassword($testPassword)) {
        echo "✅ Senha VÁLIDA!\n";
    } else {
        echo "❌ Senha INVÁLIDA\n";
    }
    echo "\n";
}

// Gerar novo hash para 'admin'
echo "🔧 Gerando novo hash para 'admin'...\n";
$newHash = Yii::$app->security->generatePasswordHash('admin');
echo "Novo hash: " . substr($newHash, 0, 30) . "...\n";

// Testar o novo hash
if (Yii::$app->security->validatePassword('admin', $newHash)) {
    echo "✅ Novo hash é válido!\n";
    
    // Atualizar no banco
    $user->password_hash = $newHash;
    $user->auth_key = Yii::$app->security->generateRandomString();
    
    if ($user->save()) {
        echo "✅ Senha atualizada no banco de dados!\n";
    } else {
        echo "❌ Erro ao salvar: " . json_encode($user->errors) . "\n";
    }
} else {
    echo "❌ Novo hash é inválido\n";
}
