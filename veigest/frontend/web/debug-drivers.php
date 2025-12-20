<?php
// Simular o controlador drivers e ver se há erros
$configFile = 'common/config/main-local.php';
$config = require __DIR__ . '/' . $configFile;

$dbConfig = $config['components']['db'];
$dsn = $dbConfig['dsn'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html>\n<html>\n<head>\n<title>Debug Controlador Drivers</title>\n<style>body{font-family:Arial;margin:20px;}table{border-collapse:collapse;width:100%;}td,th{border:1px solid #ccc;padding:8px;text-align:left;}</style>\n</head>\n<body>\n";
    
    echo "<h1>Debug - Controlador Drivers</h1>\n";
    
    // 1. Verificar if Yii pode ser carregado
    echo "<h2>1. Teste de Carregamento do Yii</h2>\n";
    try {
        require __DIR__ . '/vendor/autoload.php';
        echo "<p>✅ Autoload carregado</p>\n";
    } catch (Exception $e) {
        echo "<p>❌ Erro ao carregar autoload: " . $e->getMessage() . "</p>\n";
    }
    
    // 2. Simular a query do controlador
    echo "<h2>2. Teste de Query do Controlador</h2>\n";
    $companyId = 1;
    echo "<p>Company ID: <strong>$companyId</strong></p>\n";
    
    $sql = "SELECT id, name, email, phone, license_number, status FROM users WHERE company_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$companyId]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total encontrado: <strong>" . count($result) . "</strong></p>\n";
    
    if (!empty($result)) {
        echo "<h3>Dados da Query:</h3>\n";
        echo "<table>\n";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefone</th><th>Licença</th><th>Status</th></tr>\n";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['license_number'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // 3. Verificar se a view existe
    echo "<h2>3. Teste de Ficheiros</h2>\n";
    $viewFile = __DIR__ . '/frontend/views/dashboard/drivers.php';
    if (file_exists($viewFile)) {
        echo "<p>✅ View file existe: <code>$viewFile</code></p>\n";
        echo "<p>Tamanho: " . filesize($viewFile) . " bytes</p>\n";
    } else {
        echo "<p>❌ View file NÃO existe: <code>$viewFile</code></p>\n";
    }
    
    // 4. Verificar se o controlador existe
    $controllerFile = __DIR__ . '/frontend/controllers/DashboardController.php';
    if (file_exists($controllerFile)) {
        echo "<p>✅ Controller file existe: <code>$controllerFile</code></p>\n";
    } else {
        echo "<p>❌ Controller file NÃO existe: <code>$controllerFile</code></p>\n";
    }
    
    // 5. Verificar se o modelo Driver existe
    $modelFile = __DIR__ . '/frontend/models/Driver.php';
    if (file_exists($modelFile)) {
        echo "<p>✅ Model file existe: <code>$modelFile</code></p>\n";
    } else {
        echo "<p>❌ Model file NÃO existe: <code>$modelFile</code></p>\n";
    }
    
    echo "</body>\n</html>";
    
} catch (Exception $e) {
    echo "ERRO: " . htmlspecialchars($e->getMessage());
}
