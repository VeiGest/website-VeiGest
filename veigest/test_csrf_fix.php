<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// Configurar aplicação frontend
$config = require __DIR__ . '/frontend/config/main.php';
if (file_exists(__DIR__ . '/frontend/config/main-local.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/frontend/config/main-local.php');
}
$commonConfig = require __DIR__ . '/common/config/main.php';
if (file_exists(__DIR__ . '/common/config/main-local.php')) {
    $commonConfig = yii\helpers\ArrayHelper::merge($commonConfig, require __DIR__ . '/common/config/main-local.php');
}
$config = yii\helpers\ArrayHelper::merge($commonConfig, $config);

echo "🔧 Testando configuração CSRF e limpando sessões...\n\n";

try {
    $app = new \yii\web\Application($config);
    
    echo "✅ Aplicação iniciada com sucesso\n";
    
    // Verificar configuração CSRF
    $request = Yii::$app->request;
    echo "🔍 CSRF Validation habilitado: " . ($request->enableCsrfValidation ? 'SIM' : 'NÃO') . "\n";
    echo "🔍 CSRF Cookie Name: " . $request->csrfCookie['name'] . "\n";
    echo "🔍 CSRF Param: " . $request->csrfParam . "\n";
    
    // Limpar runtime cache e sessões
    $runtimePath = __DIR__ . '/frontend/runtime';
    echo "\n🧹 Limpando cache e sessões em: $runtimePath\n";
    
    if (is_dir($runtimePath . '/cache')) {
        $files = glob($runtimePath . '/cache/*');
        foreach($files as $file) {
            if(is_file($file)) {
                unlink($file);
                echo "  - Removido: " . basename($file) . "\n";
            }
        }
    }
    
    // Testar geração de token CSRF
    echo "\n🔑 Testando geração de token CSRF...\n";
    $csrfToken = $request->getCsrfToken();
    echo "  Token gerado: " . substr($csrfToken, 0, 20) . "...\n";
    
    echo "\n✅ Teste completado com sucesso!\n";
    echo "🌐 Tente fazer login novamente em: http://localhost/site/login\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Ficheiro: " . $e->getFile() . ":" . $e->getLine() . "\n";
}