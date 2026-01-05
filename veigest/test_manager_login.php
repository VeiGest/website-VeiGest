<?php
// Test login credentials directly
$pdo = new PDO(
    'mysql:host=localhost;dbname=veigest;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Get the manager user
$stmt = $pdo->query("SELECT id, username, password_hash, status FROM users WHERE username = 'manager'");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== Manager User ===\n";
echo "ID: " . $user['id'] . "\n";
echo "Username: " . $user['username'] . "\n";
echo "Hash: " . substr($user['password_hash'], 0, 20) . "...\n";
echo "Status: " . $user['status'] . "\n";

// Test password verification
$testPassword = 'manager123';
echo "\n=== Testing Password ===\n";
echo "Input: $testPassword\n";
$isValid = password_verify($testPassword, $user['password_hash']);
echo "Valid: " . ($isValid ? 'YES' : 'NO') . "\n";

// List all users
echo "\n=== All Users ===\n";
$stmt = $pdo->query("SELECT id, username, status FROM users ORDER BY id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, Username: {$row['username']}, Status: {$row['status']}\n";
}
?>
