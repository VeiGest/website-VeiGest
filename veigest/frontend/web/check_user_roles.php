<?php
// Inicia a aplicação Yii
require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/main.php');
$app = new yii\web\Application($config);

echo "=== Verificando Usuário Logado ===\n\n";

if (Yii::$app->user->isGuest) {
    echo "❌ Nenhum usuário logado\n";
} else {
    $userId = Yii::$app->user->id;
    echo "✓ Usuário ID: " . $userId . "\n";
    
    // Verifica roles atribuídas
    $authManager = Yii::$app->authManager;
    $roles = $authManager->getRolesByUser($userId);
    
    echo "\n=== Roles Atribuídos ===\n";
    if (empty($roles)) {
        echo "❌ Nenhum role atribuído!\n";
    } else {
        foreach ($roles as $role) {
            echo "✓ " . $role->name . " - " . $role->description . "\n";
        }
    }
    
    // Verifica permissões
    echo "\n=== Permissões ===\n";
    $permissions = [
        'maintenances.view',
        'maintenances.create',
        'maintenances.update',
        'maintenances.delete'
    ];
    
    foreach ($permissions as $perm) {
        $can = Yii::$app->user->can($perm);
        echo ($can ? "✓" : "❌") . " " . $perm . "\n";
    }
}
