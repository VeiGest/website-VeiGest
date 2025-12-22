<?php
// Script simples para verificar RBAC do usuário
define('YII_DEBUG', true);
define('YII_ENV', 'dev');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

// Configuração mínima
$config = [
    'id' => 'check-roles',
    'basePath' => __DIR__ . '/..',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=veigest',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'db' => 'db',
        ],
    ],
];

$app = new yii\web\Application($config);

// Busca usuários
$users = Yii::$app->db->createCommand('SELECT id, username FROM {{%user}}')->queryAll();

echo "<pre>\n";
echo "=== Usuários e Roles ===\n\n";

foreach ($users as $user) {
    echo "Usuário: {$user['username']} (ID: {$user['id']})\n";
    
    $roles = Yii::$app->db->createCommand(
        'SELECT item_name FROM {{%auth_assignment}} WHERE user_id = :user_id'
    )->bindValue(':user_id', $user['id'])->queryColumn();
    
    if (empty($roles)) {
        echo "  ❌ Nenhum role atribuído!\n";
    } else {
        foreach ($roles as $role) {
            echo "  ✓ {$role}\n";
        }
    }
    echo "\n";
}

echo "\n=== Roles Disponíveis ===\n";
$availableRoles = Yii::$app->db->createCommand(
    'SELECT name, description FROM {{%auth_item}} WHERE type = 1'
)->queryAll();

foreach ($availableRoles as $role) {
    echo "✓ {$role['name']} - {$role['description']}\n";
}

echo "\n=== Permissões de Manutenção ===\n";
$maintenancePerms = Yii::$app->db->createCommand(
    "SELECT name, description FROM {{%auth_item}} WHERE type = 2 AND name LIKE 'maintenances.%'"
)->queryAll();

foreach ($maintenancePerms as $perm) {
    echo "✓ {$perm['name']} - {$perm['description']}\n";
}

echo "\n</pre>";
