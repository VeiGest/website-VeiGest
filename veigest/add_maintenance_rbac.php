<?php
/**
 * Script para adicionar permissões de manutenção ao RBAC
 * Execute: php add_maintenance_rbac.php
 */

// Bootstrap do Yii
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    ['id' => 'rbac-maintenance-script', 'basePath' => __DIR__]
);

new yii\web\Application($config);

$auth = Yii::$app->authManager;

echo "=== Adicionando permissões de manutenção ===\n\n";

// Criar permissões
$permissions = [
    'maintenances.view' => 'Ver manutenções',
    'maintenances.create' => 'Criar manutenções',
    'maintenances.update' => 'Atualizar manutenções',
    'maintenances.delete' => 'Eliminar manutenções',
];

foreach ($permissions as $name => $description) {
    $permission = $auth->getPermission($name);
    if (!$permission) {
        $permission = $auth->createPermission($name);
        $permission->description = $description;
        $auth->add($permission);
        echo "✓ Permissão criada: $name - $description\n";
    } else {
        echo "- Permissão já existe: $name\n";
    }
}

echo "\n=== Atribuindo permissões aos roles ===\n\n";

// Atribuir permissões ao role 'gestor'
$gestor = $auth->getRole('gestor');
if ($gestor) {
    foreach ($permissions as $name => $description) {
        $permission = $auth->getPermission($name);
        if (!$auth->hasChild($gestor, $permission)) {
            $auth->addChild($gestor, $permission);
            echo "✓ Permissão '$name' atribuída ao role 'gestor'\n";
        } else {
            echo "- Role 'gestor' já tem permissão '$name'\n";
        }
    }
} else {
    echo "⚠ Role 'gestor' não encontrado!\n";
}

// Atribuir permissões ao role 'admin'
$admin = $auth->getRole('admin');
if ($admin) {
    foreach ($permissions as $name => $description) {
        $permission = $auth->getPermission($name);
        if (!$auth->hasChild($admin, $permission)) {
            $auth->addChild($admin, $permission);
            echo "✓ Permissão '$name' atribuída ao role 'admin'\n";
        } else {
            echo "- Role 'admin' já tem permissão '$name'\n";
        }
    }
} else {
    echo "⚠ Role 'admin' não encontrado!\n";
}

echo "\n=== Permissões configuradas com sucesso! ===\n";
echo "\nAgora recarregue a página do navegador (Ctrl+F5)\n";
