<?php
$dsn = 'mysql:host=localhost;dbname=veigest';
$db = new PDO($dsn, 'root', '');

// Inserir uma empresa se não existir
$company = $db->query('SELECT id FROM companies LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$companyId = $company ? $company['id'] : 1;

// Verificar se existe um veículo
$vehicle = $db->query('SELECT id FROM vehicles WHERE company_id = ' . $companyId . ' LIMIT 1')->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    echo "❌ Nenhum veículo encontrado. Criando um...\n";
    $db->exec("
        INSERT INTO vehicles (company_id, matricula, marca, modelo, ano, tipo_combustivel, quilometragem, estado, created_at)
        VALUES ($companyId, 'TEST-001', 'Toyota', 'Corolla', 2020, 'gasolina', 50000, 'ativo', NOW())
    ");
    $vehicleId = $db->lastInsertId();
    echo "✓ Veículo criado ID: $vehicleId\n";
} else {
    $vehicleId = $vehicle['id'];
    echo "✓ Veículo existente ID: $vehicleId\n";
}

// Criar manutenção
echo "\nCriando manutenção...\n";
$db->exec("
    INSERT INTO maintenances (company_id, vehicle_id, tipo, data, custo, proxima_data, created_at)
    VALUES ($companyId, $vehicleId, 'Troca de óleo', NOW(), 50.00, DATE_ADD(NOW(), INTERVAL 6 MONTH), NOW())
");

$mainId = $db->lastInsertId();
echo "✓ Manutenção criada ID: $mainId\n";

// Listar manutenções
echo "\n=== Manutenções ===\n";
$maintenances = $db->query("SELECT id, vehicle_id, tipo, proxima_data FROM maintenances LIMIT 5")->fetchAll();
foreach ($maintenances as $m) {
    echo "ID: {$m['id']} | Veículo: {$m['vehicle_id']} | Tipo: {$m['tipo']} | Próxima: {$m['proxima_data']}\n";
}
