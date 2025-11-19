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

echo "🔧 Atualizando password do utilizador admin...\n\n";

try {
    // Gerar hash para a password 'admin'
    $newPasswordHash = Yii::$app->security->generatePasswordHash('admin');
    $newAuthKey = Yii::$app->security->generateRandomString();
    
    echo "🔐 Novo hash gerado: " . substr($newPasswordHash, 0, 30) . "...\n";
    
    // Atualizar diretamente na base de dados
    $result = Yii::$app->db->createCommand()
        ->update('user', [
            'password_hash' => $newPasswordHash,
            'auth_key' => $newAuthKey,
            'updated_at' => time()
        ], ['username' => 'admin'])
        ->execute();
    
    if ($result > 0) {
        echo "✅ Password do utilizador 'admin' atualizada com sucesso!\n";
        echo "🌐 Acesso: http://localhost/site/login\n";
        echo "👤 Username: admin\n";
        echo "🔐 Password: admin\n";
        echo "🔧 Backend: http://localhost:8080/site/login\n";
    } else {
        echo "❌ Nenhum utilizador foi atualizado. Verifique se existe 'admin'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}