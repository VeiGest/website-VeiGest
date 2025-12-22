<?php
define('YII_DEBUG', true);
define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// Carregamento dos arquivos de configuração
require __DIR__ . '/frontend/config/bootstrap.php';
require __DIR__ . '/common/config/bootstrap.php';

// Configuração
$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/frontend/config/main.php',
    require __DIR__ . '/frontend/config/main-local.php'
);

$app = new yii\web\Application($config);

// Simular login do user admin
Yii::$app->user->setIdentity(new common\models\User(['id' => 1]));

// Buscar uma manutenção
$maintenance = frontend\models\Maintenance::findOne(1);

if (!$maintenance) {
    echo "❌ Nenhuma manutenção encontrada\n";
    exit;
}

echo "=== Testando Renderização de Botões ===\n\n";
echo "Manutenção ID: " . $maintenance->id . "\n";
echo "Usuário logado: " . Yii::$app->user->id . "\n";
echo "Role do usuário: ";

$roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
echo implode(', ', array_keys($roles)) . "\n\n";

echo "=== Testando Can() ===\n";
echo "maintenances.view: " . (Yii::$app->user->can('maintenances.view') ? "✓ SIM" : "❌ NÃO") . "\n";
echo "maintenances.update: " . (Yii::$app->user->can('maintenances.update') ? "✓ SIM" : "❌ NÃO") . "\n";
echo "maintenances.delete: " . (Yii::$app->user->can('maintenances.delete') ? "✓ SIM" : "❌ NÃO") . "\n";

echo "\n=== Testando Botões ===\n";

// Testar view button
echo "\nBotão VIEW:\n";
if (!Yii::$app->user->can('maintenances.view')) {
    echo "❌ Bloqueado por RBAC\n";
} else {
    $btn = yii\helpers\Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $maintenance->id], ['class' => 'btn btn-sm btn-info', 'title' => 'Ver']);
    echo "✓ Renderizado: " . htmlspecialchars($btn) . "\n";
}

// Testar complete button
echo "\nBotão COMPLETE:\n";
if (!Yii::$app->user->can('maintenances.update')) {
    echo "❌ Bloqueado por RBAC\n";
} else {
    if ($maintenance->next_date === null) {
        echo "❌ Bloqueado: next_date é null (já concluído)\n";
    } else {
        $btn = yii\helpers\Html::a('<i class="fas fa-check"></i>', ['complete', 'id' => $maintenance->id], [
            'class' => 'btn btn-sm btn-success',
            'title' => 'Concluir',
            'data' => [
                'confirm' => 'Tem a certeza que pretende marcar esta manutenção como concluída?',
                'method' => 'post',
            ],
        ]);
        echo "✓ Renderizado: " . htmlspecialchars($btn) . "\n";
    }
}

// Testar update button
echo "\nBotão UPDATE:\n";
if (!Yii::$app->user->can('maintenances.update')) {
    echo "❌ Bloqueado por RBAC\n";
} else {
    $btn = yii\helpers\Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $maintenance->id], ['class' => 'btn btn-sm btn-warning', 'title' => 'Editar']);
    echo "✓ Renderizado: " . htmlspecialchars($btn) . "\n";
}

// Testar delete button
echo "\nBotão DELETE:\n";
if (!Yii::$app->user->can('maintenances.delete')) {
    echo "❌ Bloqueado por RBAC\n";
} else {
    $btn = yii\helpers\Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $maintenance->id], [
        'class' => 'btn btn-sm btn-danger',
        'title' => 'Eliminar',
        'data' => [
            'confirm' => 'Tem a certeza que pretende eliminar esta manutenção?',
            'method' => 'post',
        ],
    ]);
    echo "✓ Renderizado: " . htmlspecialchars($btn) . "\n";
}
