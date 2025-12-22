<?php
$dsn = 'mysql:host=localhost;dbname=veigest';
$db = new PDO($dsn, 'root', '');

echo "=== Verificando Permissões do Role 'admin' ===\n\n";

// Buscar role_id para 'admin'
$roleId = $db->query("SELECT name FROM auth_item WHERE name = 'admin'")->fetch(PDO::FETCH_ASSOC);

if (!$roleId) {
    echo "❌ Role 'admin' não encontrado\n";
    exit;
}

// Buscar todas as permissões atribuídas ao role admin
$perms = $db->query(
    "SELECT child FROM auth_item_child WHERE parent = 'admin' AND child LIKE 'maintenances.%'"
)->fetchAll(PDO::FETCH_COLUMN);

echo "Permissões diretas de manutenção no role 'admin':\n";
if (empty($perms)) {
    echo "❌ NENHUMA permissão encontrada!\n";
} else {
    foreach ($perms as $p) {
        echo "✓ $p\n";
    }
}

echo "\n=== Todas as permissões do role 'admin' ===\n";
$allPerms = $db->query(
    "SELECT child FROM auth_item_child WHERE parent = 'admin'"
)->fetchAll(PDO::FETCH_COLUMN);
foreach ($allPerms as $p) {
    echo "- $p\n";
}
