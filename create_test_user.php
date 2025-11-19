<?php
/**
 * Script para criar utilizador de teste VeiGest
 */

// Carregar Yii2
require_once __DIR__ . '/veigest/vendor/autoload.php';
require_once __DIR__ . '/veigest/vendor/yiisoft/yii2/Yii.php';

// Configurar aplicação
$config = require __DIR__ . '/veigest/console/config/main.php';
$localConfig = __DIR__ . '/veigest/console/config/main-local.php';
if (file_exists($localConfig)) {
    $config = \yii\helpers\ArrayHelper::merge($config, require $localConfig);
}

$commonConfig = require __DIR__ . '/veigest/common/config/main.php';
$commonLocalConfig = __DIR__ . '/veigest/common/config/main-local.php';
if (file_exists($commonLocalConfig)) {
    $commonConfig = \yii\helpers\ArrayHelper::merge($commonConfig, require $commonLocalConfig);
}

$config = \yii\helpers\ArrayHelper::merge($commonConfig, $config);

// Criar aplicação
new \yii\console\Application($config);

// Usar namespace comum
use common\models\User;

echo "🚀 Criando utilizador de teste VeiGest...\n";

try {
    // Verificar se já existe
    $existingUser = User::findByUsername('admin');
    if ($existingUser) {
        echo "✅ Utilizador 'admin' já existe (ID: {$existingUser->id})\n";
        echo "📧 Email: {$existingUser->email}\n";
        echo "🔑 Para alterar a password, elimine primeiro este utilizador\n";
        exit(0);
    }

    // Criar novo utilizador
    $user = new User();
    $user->nome = 'admin';
    $user->username = 'admin';
    $user->email = 'admin@veigest.pt';
    $user->status = User::STATUS_ACTIVE;
    $user->company_id = 1;
    
    // Definir password
    $user->setPassword('123456');
    $user->generateAuthKey();
    
    if ($user->save()) {
        echo "✅ Utilizador criado com sucesso!\n";
        echo "👤 Username: admin\n";
        echo "📧 Email: admin@veigest.pt\n";
        echo "🔐 Password: 123456\n";
        echo "🏢 Company ID: 1\n";
        echo "🆔 User ID: {$user->id}\n";
        echo "\n";
        echo "🌐 Acesso Frontend: http://localhost/site/login\n";
        echo "🔧 Acesso Backend: http://localhost:8080/site/login\n";
    } else {
        echo "❌ Erro ao criar utilizador:\n";
        foreach ($user->getErrors() as $field => $errors) {
            foreach ($errors as $error) {
                echo "  - {$field}: {$error}\n";
            }
        }
    }

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}