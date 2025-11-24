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

echo "🔍 Verificando utilizadores na base de dados...\n\n";

try {
    $users = Yii::$app->db->createCommand('SELECT * FROM user')->queryAll();
    echo "✅ Utilizadores encontrados: " . count($users) . "\n\n";
    
    foreach ($users as $user) {
        echo "👤 ID: {$user['id']}\n";
        echo "   Username: {$user['username']}\n";
        echo "   Nome: {$user['name']}\n";
        echo "   Email: {$user['email']}\n";
        echo "   Status: {$user['status']}\n";
        echo "   Company ID: {$user['company_id']}\n";
        echo "   Password Hash: " . substr($user['password_hash'], 0, 20) . "...\n";
        echo "   ─────────────────────────────────────\n";
    }
    
    // Tentar login com o primeiro utilizador
    if (count($users) > 0) {
        $firstUser = $users[0];
        echo "\n🔐 Testando passwords para utilizador: {$firstUser['username']}\n";
        
        // Carregar o modelo User
        $user = \common\models\User::findOne($firstUser['id']);
        
        $testPasswords = ['admin', '123456', 'password', 'veigest'];
        foreach ($testPasswords as $testPass) {
            if ($user && $user->validatePassword($testPass)) {
                echo "✅ Password '$testPass' FUNCIONA!\n";
            } else {
                echo "❌ Password '$testPass' não funciona\n";
            }
        }
        
        // Atualizar password para 'admin'
        echo "\n🔧 Definindo password como 'admin'...\n";
        $user->setPassword('admin');
        if ($user->save()) {
            echo "✅ Password atualizada para 'admin'\n";
            echo "🌐 Pode agora fazer login em: http://localhost/site/login\n";
            echo "👤 Username: {$user->username}\n";
            echo "🔐 Password: admin\n";
        } else {
            echo "❌ Erro ao atualizar password\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}