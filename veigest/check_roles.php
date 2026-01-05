<?php
$pdo = new PDO('mysql:host=localhost;dbname=veigest;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== RBAC Roles ===\n";
$stmt = $pdo->query('SELECT * FROM auth_item WHERE type = 1');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Role: " . $row['name'] . "\n";
}

echo "\n=== User Assignments ===\n";
$stmt = $pdo->query('SELECT u.id, u.username, aa.item_name FROM users u LEFT JOIN auth_assignment aa ON u.id = aa.user_id ORDER BY u.id');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "User ID: " . $row['id'] . ", Username: " . $row['username'] . ", Role: " . ($row['item_name'] ?? 'N/A') . "\n";
}
?>
