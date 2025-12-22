<?php
// Script para criar usuário gestor
$dsn = 'mysql:host=localhost;dbname=veigest';
$db = new PDO($dsn, 'root', '');

echo "=== Criando usuário Gestor ===\n\n";

// Verificar se já existe
$exists = $db->query("SELECT id FROM users WHERE email = 'gestor@veigest.com'")->fetch();

if ($exists) {
    echo "❌ Usuário gestor@veigest.com já existe (ID: {$exists['id']})\n";
    exit;
}

// Password hash para "gestor123"
$passwordHash = password_hash('gestor123', PASSWORD_DEFAULT);
$authKey = md5('gestor@veigest.com' . time());

// Inserir usuário
$stmt = $db->prepare("
    INSERT INTO users (company_id, nome, email, password_hash, estado, auth_key, created_at)
    VALUES (1, 'Gestor Teste', 'gestor@veigest.com', :password_hash, 'ativo', :auth_key, NOW())
");

$stmt->execute([
    ':password_hash' => $passwordHash,
    ':auth_key' => $authKey
]);

$userId = $db->lastInsertId();

echo "✓ Usuário criado: ID $userId\n";
echo "  Nome: Gestor Teste\n";
echo "  Email: gestor@veigest.com\n";
echo "  Password: gestor123\n\n";

// Atribuir role "gestor"
$db->exec("
    INSERT INTO auth_assignment (item_name, user_id, created_at)
    VALUES ('gestor', $userId, " . time() . ")
");

echo "✓ Role 'gestor' atribuído\n\n";

echo "=== Sucesso! ===\n";
echo "Faça login com:\n";
echo "  Email: gestor@veigest.com\n";
echo "  Password: gestor123\n";
