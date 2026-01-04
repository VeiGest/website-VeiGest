<?php
// Script simples para atualizar senha do admin
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// Configurar aplicação console
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

echo "🔧 Atualizando senha do admin...\n";

try {
    // Conectar ao banco
    $db = $app->db;
    
    // Gerar novo hash para senha 'admin'
    $passwordHash = Yii::$app->security->generatePasswordHash('admin');
    $authKey = Yii::$app->security->generateRandomString();
    
    echo "Hash gerado: " . substr($passwordHash, 0, 30) . "...\n";
    
    // Atualizar usuário admin
    $result = $db->createCommand()
        ->update('users', [
            'password_hash' => $passwordHash,
            'auth_key' => $authKey
        ], ['username' => 'admin'])
        ->execute();
    
    if ($result > 0) {
        echo "✅ Senha do admin atualizada!\n";
        echo "Username: admin\n";
        echo "Password: admin\n";
        
        // Testar o hash
        if (Yii::$app->security->validatePassword('admin', $passwordHash)) {
            echo "✅ Validação de senha: OK\n";
        } else {
            echo "❌ Validação de senha: FALHOU\n";
        }
    } else {
        echo "❌ Nenhum registro foi atualizado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
