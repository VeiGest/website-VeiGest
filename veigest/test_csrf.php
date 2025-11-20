<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// Configurar aplicação
$config = require __DIR__ . '/frontend/config/main.php';
if (file_exists(__DIR__ . '/frontend/config/main-local.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/frontend/config/main-local.php');
}
$commonConfig = require __DIR__ . '/common/config/main.php';
if (file_exists(__DIR__ . '/common/config/main-local.php')) {
    $commonConfig = yii\helpers\ArrayHelper::merge($commonConfig, require __DIR__ . '/common/config/main-local.php');
}
$config = yii\helpers\ArrayHelper::merge($commonConfig, $config);

echo "🔍 Testando configuração CSRF...\n\n";

try {
    $app = new \yii\web\Application($config);
    
    // Verificar configuração do request
    $request = Yii::$app->request;
    echo "✅ Request component configurado\n";
    echo "   CSRF Param: " . $request->csrfParam . "\n";
    echo "   Cookie Validation Key: " . (empty($request->cookieValidationKey) ? "❌ VAZIO" : "✅ Configurado") . "\n";
    
    // Tentar gerar um token CSRF
    $csrfToken = $request->getCsrfToken();
    echo "   CSRF Token gerado: " . substr($csrfToken, 0, 20) . "...\n";
    
    // Verificar se o modelo User existe e está acessível
    $user = new \common\models\User();
    echo "✅ Modelo User carregado com sucesso\n";
    
    // Verificar LoginForm
    $loginForm = new \common\models\LoginForm();
    echo "✅ Modelo LoginForm carregado com sucesso\n";
    
    echo "\n🎯 Configuração parece estar correta.\n";
    echo "💡 O problema pode estar na validação CSRF durante o POST.\n";
    echo "🔧 Verifique se os cookies estão sendo aceites no navegador.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}