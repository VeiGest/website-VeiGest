<?php
$pdo = new PDO('mysql:host=localhost;dbname=veigest;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== Fuel Logs Count ===\n";
$stmt = $pdo->query('SELECT COUNT(*) as count FROM fuel_logs');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total fuel logs: " . $row['count'] . "\n";

echo "\n=== Recent Fuel Logs ===\n";
$stmt = $pdo->query('SELECT id, company_id, vehicle_id, date, liters, value, price_per_liter FROM fuel_logs ORDER BY date DESC LIMIT 5');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, Company: {$row['company_id']}, Date: {$row['date']}, Liters: {$row['liters']}, Value: {$row['value']}\n";
}

echo "\n=== Maintenance Count ===\n";
$stmt = $pdo->query('SELECT COUNT(*) as count FROM maintenances');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total maintenances: " . $row['count'] . "\n";

echo "\n=== Recent Maintenances ===\n";
$stmt = $pdo->query('SELECT id, company_id, vehicle_id, date, type, cost FROM maintenances ORDER BY date DESC LIMIT 5');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, Company: {$row['company_id']}, Date: {$row['date']}, Type: {$row['type']}, Cost: {$row['cost']}\n";
}
?>
