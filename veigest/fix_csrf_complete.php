<?php
/**
 * Script para resolver problemas CSRF no VeiGest
 */

echo "🔧 VeiGest - Resolução de problemas CSRF\n";
echo "========================================\n\n";

// 1. Limpar cache de runtime
$frontendRuntime = __DIR__ . '/frontend/runtime';
$backendRuntime = __DIR__ . '/backend/runtime';

echo "🧹 Limpando cache de sessões e runtime...\n";

$directories = [
    $frontendRuntime . '/cache',
    $frontendRuntime . '/debug', 
    $backendRuntime . '/cache',
    $backendRuntime . '/debug'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                echo "  ✅ Removido: " . basename($file) . "\n";
            }
        }
        echo "  📁 Limpado: " . basename($dir) . "/\n";
    }
}

// 2. Verificar configuração de sessão
echo "\n🔍 Verificando configuração de sessão...\n";

$frontendConfig = __DIR__ . '/frontend/config/main.php';
$content = file_get_contents($frontendConfig);

if (strpos($content, 'session') !== false) {
    echo "  ✅ Configuração de sessão encontrada\n";
} else {
    echo "  ⚠️  Configuração de sessão não encontrada\n";
}

// 3. Verificar se há conflitos de cookie
echo "\n🍪 Verificando configuração de cookies...\n";
if (strpos($content, 'csrfParam') !== false) {
    echo "  ✅ Parâmetro CSRF configurado\n";
} else {
    echo "  ⚠️  Parâmetro CSRF não configurado\n";
}

// 4. Criar um teste HTML simples
$testHtml = '<!DOCTYPE html>
<html>
<head>
    <title>Teste CSRF - VeiGest</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Teste de Login VeiGest</h1>
    <p>Use este formulário simples para testar:</p>
    <form action="/site/login" method="POST">
        <p>
            <label>Nome de utilizador:</label><br>
            <input type="text" name="LoginForm[nome]" value="admin" required>
        </p>
        <p>
            <label>Senha:</label><br>
            <input type="password" name="LoginForm[password]" value="admin" required>
        </p>
        <p>
            <input type="hidden" name="_csrf-frontend" value="<?= \\Yii::$app->request->getCsrfToken() ?>">
            <input type="submit" value="Login">
        </p>
    </form>
    <p><a href="/site/login">← Voltar ao login normal</a></p>
</body>
</html>';

file_put_contents(__DIR__ . '/frontend/web/test-login.html', str_replace('<?= \\Yii::$app->request->getCsrfToken() ?>', 'test-token-' . time(), $testHtml));

echo "\n✅ Limpeza completada!\n";
echo "🌐 Teste as seguintes opções:\n";
echo "   1. Login normal: http://localhost/site/login\n";  
echo "   2. Teste simples: http://localhost/test-login.html\n";
echo "\n💡 Dicas:\n";
echo "   - Limpe o cache do navegador (Ctrl+F5)\n";
echo "   - Tente usar modo incógnito\n";
echo "   - Verifique se há cookies antigos\n\n";