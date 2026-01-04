# 📄 Views e Templates

## Visão Geral

As views estão em `frontend/views/` e utilizam sintaxe PHP com helpers do Yii2. A organização segue a convenção `controller/action.php`.

## Estrutura de Diretórios

```
frontend/views/
├── layouts/                # Layouts base
│   ├── main.php           # Layout público
│   ├── dashboard.php      # Layout autenticado
│   └── login.php          # Layout de login
├── site/                   # Views do SiteController
│   ├── index.php          # Homepage
│   ├── login.php          # Login
│   ├── signup.php         # Registo
│   ├── contact.php        # Contacto
│   └── error.php          # Erro
├── dashboard/              # Views do DashboardController
│   ├── index.php          # Dashboard principal
│   ├── vehicles.php       # Lista de veículos
│   ├── maintenance.php    # Manutenções
│   └── alerts.php         # Alertas
├── report/                 # Views do ReportController
│   ├── index.php          # Relatório geral
│   ├── maintenance.php    # Relatório manutenção
│   └── fuel.php           # Relatório combustível
└── document/               # Views do DocumentController
    ├── index.php          # Lista documentos
    ├── create.php         # Upload documento
    └── view.php           # Ver documento
```

---

## Anatomia de uma View

### Estrutura Básica

```php
<?php
/**
 * @var yii\web\View $this
 * @var common\models\Vehicle[] $vehicles
 */

use yii\helpers\Html;

// Título da página
$this->title = 'Veículos';

// Breadcrumbs
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="vehicles-index">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <!-- Conteúdo da página -->
</div>
```

---

## Dashboard Principal

### `dashboard/index.php`

```php
<?php
/**
 * @var yii\web\View $this
 * @var int $totalVehicles
 * @var int $totalDrivers
 * @var int $activeAlerts
 * @var float $monthlyCost
 * @var array $fuelMonthly
 * @var array $fleetState
 * @var common\models\Alert[] $recentAlerts
 */

use yii\helpers\Html;

$this->title = 'Dashboard';
?>

<div class="dashboard-content">
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Veículos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Veículos</p>
                    <h2 class="text-3xl font-bold text-gray-800">
                        <?= $totalVehicles ?>
                    </h2>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600">...</svg>
                </div>
            </div>
        </div>
        
        <!-- Total Condutores -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Condutores</p>
                    <h2 class="text-3xl font-bold text-gray-800">
                        <?= $totalDrivers ?>
                    </h2>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600">...</svg>
                </div>
            </div>
        </div>
        
        <!-- Alertas Activos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Alertas Activos</p>
                    <h2 class="text-3xl font-bold text-red-600">
                        <?= $activeAlerts ?>
                    </h2>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <svg class="w-8 h-8 text-red-600">...</svg>
                </div>
            </div>
        </div>
        
        <!-- Custo Mensal -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Custo Mensal</p>
                    <h2 class="text-3xl font-bold text-gray-800">
                        <?= Yii::$app->formatter->asCurrency($monthlyCost, 'EUR') ?>
                    </h2>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600">...</svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Consumo Mensal -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Consumo de Combustível</h3>
            <canvas id="fuelChart" height="250"></canvas>
        </div>
        
        <!-- Estado da Frota -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Estado da Frota</h3>
            <canvas id="fleetChart" height="250"></canvas>
        </div>
    </div>
    
    <!-- Alertas Recentes -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h3 class="text-lg font-semibold">Alertas Recentes</h3>
        </div>
        <div class="divide-y">
            <?php foreach ($recentAlerts as $alert): ?>
            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full 
                        <?= $alert->priority === 'high' ? 'bg-red-500' : 
                           ($alert->priority === 'medium' ? 'bg-yellow-500' : 'bg-green-500') ?>">
                    </span>
                    <div>
                        <p class="font-medium"><?= Html::encode($alert->title) ?></p>
                        <p class="text-sm text-gray-500">
                            <?= Yii::$app->formatter->asRelativeTime($alert->created_at) ?>
                        </p>
                    </div>
                </div>
                <span class="text-sm px-3 py-1 rounded-full 
                    <?= $alert->status === 'active' ? 'bg-red-100 text-red-600' : 'bg-gray-100' ?>">
                    <?= ucfirst($alert->status) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// JavaScript para gráficos
$fuelLabels = json_encode(array_column($fuelMonthly, 'month_label'));
$fuelData = json_encode(array_column($fuelMonthly, 'total_liters'));
$fleetLabels = json_encode(array_keys($fleetState));
$fleetData = json_encode(array_values($fleetState));

$script = <<<JS
// Gráfico de Combustível
new Chart(document.getElementById('fuelChart'), {
    type: 'bar',
    data: {
        labels: {$fuelLabels},
        datasets: [{
            label: 'Litros',
            data: {$fuelData},
            backgroundColor: '#3B82F6',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

// Gráfico Estado da Frota
new Chart(document.getElementById('fleetChart'), {
    type: 'doughnut',
    data: {
        labels: {$fleetLabels},
        datasets: [{
            data: {$fleetData},
            backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#6B7280']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
    }
});
JS;

$this->registerJs($script);
?>
```

---

## Formulários

### Login Form (`site/login.php`)

```php
<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-blue-600">VeiGest</h1>
            <p class="text-gray-500">Sistema de Gestão de Frotas</p>
        </div>
        
        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'options' => ['class' => 'space-y-6'],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                'inputOptions' => ['class' => 'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500'],
                'errorOptions' => ['class' => 'text-red-500 text-sm mt-1'],
            ],
        ]); ?>
        
        <?= $form->field($model, 'username')
            ->textInput(['autofocus' => true, 'placeholder' => 'Utilizador']) ?>
        
        <?= $form->field($model, 'password')
            ->passwordInput(['placeholder' => 'Palavra-passe']) ?>
        
        <?= $form->field($model, 'rememberMe')->checkbox([
            'template' => '<div class="flex items-center">{input}{label}</div>',
            'labelOptions' => ['class' => 'ml-2 text-sm text-gray-600'],
        ]) ?>
        
        <div class="mt-6">
            <?= Html::submitButton('Entrar', [
                'class' => 'w-full py-3 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium',
                'name' => 'login-button'
            ]) ?>
        </div>
        
        <?php ActiveForm::end(); ?>
        
        <!-- Links -->
        <div class="mt-6 text-center text-sm">
            <a href="<?= Yii::$app->urlManager->createUrl(['site/signup']) ?>" 
               class="text-blue-600 hover:underline">
                Criar conta
            </a>
            <span class="mx-2">•</span>
            <a href="<?= Yii::$app->urlManager->createUrl(['site/request-password-reset']) ?>" 
               class="text-blue-600 hover:underline">
                Esqueci a palavra-passe
            </a>
        </div>
    </div>
</div>
```

---

## Tabelas com GridView

```php
<?php
use yii\grid\GridView;
use yii\helpers\Html;

?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'tableOptions' => ['class' => 'min-w-full divide-y divide-gray-200'],
    'headerRowOptions' => ['class' => 'bg-gray-50'],
    'rowOptions' => ['class' => 'hover:bg-gray-50'],
    'columns' => [
        // Coluna ID
        [
            'attribute' => 'id',
            'headerOptions' => ['class' => 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase'],
            'contentOptions' => ['class' => 'px-6 py-4 whitespace-nowrap'],
        ],
        
        // Coluna com formatação
        [
            'attribute' => 'license_plate',
            'format' => 'raw',
            'value' => function($model) {
                return Html::tag('span', $model->license_plate, [
                    'class' => 'px-2 py-1 bg-blue-100 text-blue-800 rounded font-mono'
                ]);
            },
        ],
        
        // Coluna de status com badge
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function($model) {
                $colors = [
                    'active' => 'bg-green-100 text-green-800',
                    'maintenance' => 'bg-yellow-100 text-yellow-800',
                    'inactive' => 'bg-gray-100 text-gray-800',
                ];
                $class = $colors[$model->status] ?? 'bg-gray-100';
                return Html::tag('span', ucfirst($model->status), [
                    'class' => "px-2 py-1 rounded-full text-xs font-medium $class"
                ]);
            },
            'filter' => ['active' => 'Activo', 'maintenance' => 'Manutenção', 'inactive' => 'Inactivo'],
        ],
        
        // Coluna de data formatada
        [
            'attribute' => 'created_at',
            'format' => ['date', 'php:d/m/Y H:i'],
        ],
        
        // Coluna de ações
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view} {update} {delete}',
            'buttons' => [
                'view' => function($url, $model) {
                    return Html::a('<svg class="w-5 h-5">...</svg>', $url, [
                        'class' => 'text-blue-600 hover:text-blue-800',
                        'title' => 'Ver',
                    ]);
                },
            ],
        ],
    ],
]) ?>
```

---

## Partials

### Como Criar Partials

```php
// views/dashboard/_vehicle-card.php
<?php
/**
 * @var common\models\Vehicle $vehicle
 */
use yii\helpers\Html;
?>

<div class="bg-white rounded-lg shadow p-4">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600">...</svg>
        </div>
        <div>
            <h4 class="font-semibold"><?= Html::encode($vehicle->license_plate) ?></h4>
            <p class="text-sm text-gray-500"><?= Html::encode($vehicle->brand . ' ' . $vehicle->model) ?></p>
        </div>
    </div>
    <div class="mt-3 pt-3 border-t">
        <span class="text-sm px-2 py-1 rounded 
            <?= $vehicle->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100' ?>">
            <?= ucfirst($vehicle->status) ?>
        </span>
    </div>
</div>
```

### Usar Partials

```php
// Na view principal
<?php foreach ($vehicles as $vehicle): ?>
    <?= $this->render('_vehicle-card', ['vehicle' => $vehicle]) ?>
<?php endforeach; ?>
```

---

## Widgets Personalizados

### Alert Widget

```php
// common/widgets/Alert.php
<?php
namespace common\widgets;

use Yii;
use yii\bootstrap5\Widget;

class Alert extends Widget
{
    public $alertTypes = [
        'error'   => 'bg-red-100 border-red-500 text-red-700',
        'danger'  => 'bg-red-100 border-red-500 text-red-700',
        'success' => 'bg-green-100 border-green-500 text-green-700',
        'info'    => 'bg-blue-100 border-blue-500 text-blue-700',
        'warning' => 'bg-yellow-100 border-yellow-500 text-yellow-700',
    ];
    
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $html = '';
        
        foreach ($flashes as $type => $messages) {
            if (!isset($this->alertTypes[$type])) continue;
            
            foreach ((array)$messages as $message) {
                $html .= <<<HTML
                <div class="p-4 mb-4 border-l-4 rounded {$this->alertTypes[$type]}">
                    <p>{$message}</p>
                </div>
                HTML;
            }
            $session->removeFlash($type);
        }
        
        return $html;
    }
}
```

### Usar na View

```php
<?php use common\widgets\Alert; ?>

<?= Alert::widget() ?>
```

---

## Helpers Úteis

### Html Helper

```php
use yii\helpers\Html;

// Links
Html::a('Texto', ['controller/action'], ['class' => 'btn']);

// Formulários
Html::beginForm(['action'], 'post');
Html::endForm();

// Inputs
Html::textInput('name', $value, ['class' => 'form-control']);
Html::dropDownList('name', $selected, $items);
Html::checkbox('name', $checked);

// Tags
Html::tag('div', 'Conteúdo', ['class' => 'container']);
Html::encode($text);  // Escapar HTML
```

### Url Helper

```php
use yii\helpers\Url;

Url::to(['site/login']);
Url::to(['vehicle/view', 'id' => 1]);
Url::toRoute('dashboard/index');
Url::home();
Url::current(['page' => 2]);
```

---

## Próximos Passos

- [Layouts](layouts.md)
- [Assets](assets.md)
