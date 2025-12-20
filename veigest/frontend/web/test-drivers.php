<?php
// Teste simplificado de rendering
$configFile = 'common/config/main-local.php';
$config = require __DIR__ . '/' . $configFile;

$dbConfig = $config['components']['db'];
$dsn = $dbConfig['dsn'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simular a query do controller
    $companyId = 1;
    $sql = "SELECT id, name, email, phone, license_number, status FROM users WHERE company_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$companyId]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<!DOCTYPE html>\n";
    echo "<html>\n<head>\n<title>Teste Drivers</title>\n</head>\n<body>\n";
    echo "<h1>Teste da Query de Drivers</h1>\n";
    echo "<p>Total: " . count($result) . "</p>\n";
    
    if (!empty($result)) {
        echo "<table border='1'>\n";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefone</th><th>Licença</th><th>Status</th></tr>\n";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
            echo "<td>" . htmlspecialchars($row['license_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>Nenhum resultado encontrado!</p>\n";
    }
    
    echo "</body>\n</html>";
    
} catch (Exception $e) {
    echo "ERRO: " . htmlspecialchars($e->getMessage());
}
