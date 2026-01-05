<?php
$pdo = new PDO('mysql:host=localhost;dbname=veigest;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Add fuel logs for January 2026
$fuelData = [
    ['company_id' => 1, 'vehicle_id' => 1, 'driver_id' => 5, 'date' => '2026-01-01', 'liters' => 50, 'value' => 75.00, 'price_per_liter' => 1.50, 'current_mileage' => 150000],
    ['company_id' => 1, 'vehicle_id' => 2, 'driver_id' => 6, 'date' => '2026-01-03', 'liters' => 48, 'value' => 72.00, 'price_per_liter' => 1.50, 'current_mileage' => 85000],
    ['company_id' => 1, 'vehicle_id' => 3, 'driver_id' => 7, 'date' => '2026-01-05', 'liters' => 52, 'value' => 78.00, 'price_per_liter' => 1.50, 'current_mileage' => 120000],
];

foreach ($fuelData as $fuel) {
    $stmt = $pdo->prepare('INSERT INTO fuel_logs (company_id, vehicle_id, driver_id, date, liters, value, price_per_liter, current_mileage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$fuel['company_id'], $fuel['vehicle_id'], $fuel['driver_id'], $fuel['date'], $fuel['liters'], $fuel['value'], $fuel['price_per_liter'], $fuel['current_mileage']]);
}

echo "Fuel logs added for January 2026\n";

// Add maintenance for January 2026
$maintData = [
    ['company_id' => 1, 'vehicle_id' => 1, 'type' => 'Oil Change', 'date' => '2026-01-02', 'status' => 'completed', 'cost' => 85.50, 'workshop' => 'Auto Shop', 'mileage_record' => 150100],
    ['company_id' => 1, 'vehicle_id' => 2, 'type' => 'Tire Rotation', 'date' => '2026-01-04', 'status' => 'completed', 'cost' => 120.00, 'workshop' => 'Tire Shop', 'mileage_record' => 85050],
];

foreach ($maintData as $maint) {
    $stmt = $pdo->prepare('INSERT INTO maintenances (company_id, vehicle_id, type, date, status, cost, workshop, mileage_record, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$maint['company_id'], $maint['vehicle_id'], $maint['type'], $maint['date'], $maint['status'], $maint['cost'], $maint['workshop'], $maint['mileage_record'], 'Maintenance record']);
}

echo "Maintenance records added for January 2026\n";
?>
