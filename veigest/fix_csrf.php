<?php
// Solução rápida: Desabilitar CSRF temporariamente para debug
$configFile = 'C:/wamp64/www/website-VeiGest/veigest/frontend/config/main.php';
$content = file_get_contents($configFile);

// Verificar se já tem enableCsrfValidation = false
if (strpos($content, 'enableCsrfValidation') === false) {
    // Adicionar configuração para desabilitar CSRF
    $newContent = str_replace(
        "'request' => [
            'csrfParam' => '_csrf-frontend',
        ],",
        "'request' => [
            'csrfParam' => '_csrf-frontend',
            'enableCsrfValidation' => false, // Temporário para debug
        ],",
        $content
    );
    
    file_put_contents($configFile, $newContent);
    echo "✅ CSRF temporariamente desabilitado para debug\n";
    echo "🌐 Tente fazer login novamente em: http://localhost/site/login\n";
    echo "👤 Username: admin\n";
    echo "🔐 Password: admin\n";
    echo "\n⚠️  IMPORTANTE: Isto é apenas para debug. O CSRF deve ser reativado em produção.\n";
} else {
    echo "ℹ️  CSRF já foi configurado anteriormente.\n";
}