<?php
/**
 * Script para adicionar o role 'driver' ao RBAC e atribuir a users com numero_carta
 */

// Configurar a base de dados
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'veigest';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Adicionando role 'driver' ao RBAC...</h2>";
    
    // 1. Inserir o role 'driver' em auth_item
    $checkRole = $pdo->prepare("SELECT * FROM auth_item WHERE name = 'driver'");
    $checkRole->execute();
    
    if ($checkRole->rowCount() == 0) {
        $insertRole = $pdo->prepare("
            INSERT INTO auth_item (name, type, description, created_at) 
            VALUES ('driver', 1, 'Driver/Condutor', UNIX_TIMESTAMP())
        ");
        $insertRole->execute();
        echo "<p style='color: green;'>✓ Role 'driver' criado em auth_item</p>";
    } else {
        echo "<p style='color: orange;'>✓ Role 'driver' já existe em auth_item</p>";
    }
    
    // 2. Atribuir 'driver' aos usuários que têm numero_carta preenchido
    $drivers = $pdo->prepare("
        SELECT id FROM users 
        WHERE numero_carta IS NOT NULL AND numero_carta != ''
    ");
    $drivers->execute();
    
    $driverUsers = $drivers->fetchAll(PDO::FETCH_ASSOC);
    $addedCount = 0;
    
    echo "<h3>Atribuindo role 'driver' aos users com numero_carta:</h3>";
    echo "<ul>";
    
    foreach ($driverUsers as $driver) {
        $userId = $driver['id'];
        
        // Verificar se já tem o role
        $checkAssignment = $pdo->prepare("
            SELECT * FROM auth_assignment 
            WHERE item_name = 'driver' AND user_id = ?
        ");
        $checkAssignment->execute([$userId]);
        
        if ($checkAssignment->rowCount() == 0) {
            $addRole = $pdo->prepare("
                INSERT INTO auth_assignment (item_name, user_id, created_at) 
                VALUES ('driver', ?, UNIX_TIMESTAMP())
            ");
            $addRole->execute([$userId]);
            $addedCount++;
            
            // Obter nome do usuário
            $getUser = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $getUser->execute([$userId]);
            $userName = $getUser->fetch(PDO::FETCH_ASSOC)['name'];
            
            echo "<li style='color: green;'>✓ User ID $userId ($userName) atribuído como 'driver'</li>";
        } else {
            // Obter nome do usuário
            $getUser = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $getUser->execute([$userId]);
            $userName = $getUser->fetch(PDO::FETCH_ASSOC)['name'];
            
            echo "<li style='color: orange;'>~ User ID $userId ($userName) já é 'driver'</li>";
        }
    }
    
    echo "</ul>";
    
    echo "<h3>Resumo:</h3>";
    echo "<p>Total de users com numero_carta: " . count($driverUsers) . "</p>";
    echo "<p>Novos drivers adicionados: $addedCount</p>";
    
    echo "<hr>";
    echo "<h3>Users com role 'driver':</h3>";
    echo "<ul>";
    
    $listDrivers = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.numero_carta 
        FROM users u
        INNER JOIN auth_assignment aa ON aa.user_id = u.id
        WHERE aa.item_name = 'driver'
        ORDER BY u.name
    ");
    $listDrivers->execute();
    
    $allDrivers = $listDrivers->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allDrivers as $d) {
        echo "<li>ID: {$d['id']} | Nome: {$d['name']} | Email: {$d['email']} | Número Carta: {$d['numero_carta']}</li>";
    }
    
    echo "</ul>";
    
    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>✓ Processo concluído com sucesso!</p>";
    echo "<p><a href='http://localhost/dashboard/drivers'>Ir para página de drivers</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>
