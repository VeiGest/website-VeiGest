<?php
define('YII_DEBUG', true);
define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

require __DIR__ . '/frontend/config/bootstrap.php';
require __DIR__ . '/common/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/frontend/config/main.php',
    require __DIR__ . '/frontend/config/main-local.php'
);

$app = new yii\web\Application($config);

echo "=== Testando Carregamento de Manutenções ===\n\n";

$maintenances = frontend\models\Maintenance::find()->all();

if (empty($maintenances)) {
    echo "❌ Nenhuma manutenção encontrada\n";
} else {
    foreach ($maintenances as $m) {
        echo "ID: " . $m->id . "\n";
        echo "  Tipo: " . $m->tipo . "\n";
        echo "  Data: " . $m->data . "\n";
        echo "  Proxima Data: " . $m->proxima_data . "\n";
        echo "  Custo: " . $m->custo . "\n";
        echo "  Veículo: " . ($m->vehicle ? $m->vehicle->modelo . " (" . $m->vehicle->matricula . ")" : "SEM VEÍCULO") . "\n\n";
    }
    echo "\n✓ Tudo carrega corretamente!\n";
}
