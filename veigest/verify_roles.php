<?php
$dsn = 'mysql:host=localhost;dbname=veigest';
$db = new PDO($dsn, 'root', '');

echo "=== Usuários ===\n";
$users = $db->query('SELECT id, nome FROM users LIMIT 5')->fetchAll();
foreach ($users as $u) {
    echo 'ID: ' . $u['id'] . ' - ' . $u['nome'] . "\n";
}

echo "\n=== Roles Disponíveis ===\n";
$roles = $db->query("SELECT name FROM auth_item WHERE type = 1")->fetchAll();
foreach ($roles as $r) {
    echo '- ' . $r['name'] . "\n";
}

echo "\n=== Atribuições (auth_assignment) ===\n";
$assign = $db->query('SELECT user_id, item_name FROM auth_assignment')->fetchAll();
foreach ($assign as $a) {
    echo 'User ' . $a['user_id'] . ' -> ' . $a['item_name'] . "\n";
}
