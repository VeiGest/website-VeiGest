<?php

/** @var \yii\web\View $this */

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= isset($totalVehicles) ? $totalVehicles : 0 ?></h3>
                <p>Total Veículos</p>
            </div>
            <div class="icon">
                <i class="fas fa-truck"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= isset($totalDrivers) ? $totalDrivers : 0 ?></h3>
                <p>Condutores Ativos</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tie"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 style="color: white;"><?= isset($activeAlerts) ? $activeAlerts : 0 ?></h3>
                <p style="color: white;">Alertas Pendentes</p>
            </div>
            <div class="icon">
                <i class="fas fa-bell"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 style="color: white;">€<?= number_format(isset($monthlyCost) ? $monthlyCost : 0, 2, ',', '.') ?></h3>
                <p style="color: white;">Custo Mensal</p>
            </div>
            <div class="icon">
                <i class="fas fa-euro-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Consumo de Combustível (últimos 12 meses)</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="consumptionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estado da Frota</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="stateChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Alerts and Activities -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Alertas Recentes</h3>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (!empty($recentAlerts)): ?>
                        <?php foreach ($recentAlerts as $alert): ?>
                            <div class="list-group-item">
                                <?php
                                    $icon = 'fas fa-info-circle text-info mr-2';
                                    if ($alert->type === \common\models\Alert::TYPE_DOCUMENT) $icon = 'fas fa-calendar-times text-danger mr-2';
                                    if ($alert->type === \common\models\Alert::TYPE_MAINTENANCE) $icon = 'fas fa-wrench text-warning mr-2';
                                ?>
                                <i class="<?= $icon ?>"></i>
                                <strong><?= \yii\helpers\Html::encode($alert->title) ?></strong>
                                <p class="text-muted mb-0"><?= \yii\helpers\Html::encode($alert->description) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="list-group-item text-muted">Sem alertas recentes.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Atividades Recentes</h3>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <strong>João Silva</strong> criou novo veículo
                        <p class="text-muted mb-0 text-sm">Há 1 hora</p>
                    </div>
                    <div class="list-group-item">
                        <strong>Maria Oliveira</strong> atualizou documento
                        <p class="text-muted mb-0 text-sm">Há 3 horas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top' }
    }
};

// Consumption Chart
const ctxConsumo = document.getElementById('consumptionChart').getContext('2d');
new Chart(ctxConsumo, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($fuelMonthly ?? [], 'month_label')) ?>,
        datasets: [{
            label: 'Consumo (L)',
            data: <?= json_encode(array_map('floatval', array_column($fuelMonthly ?? [], 'total_liters'))) ?>,
            borderColor: '#09BC8A',
            backgroundColor: 'rgba(9, 188, 138, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: chartOptions
});

// State Distribution Chart
const ctxState = document.getElementById('stateChart').getContext('2d');
new Chart(ctxState, {
    type: 'doughnut',
    data: {
        labels: ['Ativo', 'Manutenção', 'Inativo'],
        datasets: [{
            data: [<?= (int)($fleetState['active'] ?? 0) ?>, <?= (int)($fleetState['maintenance'] ?? 0) ?>, <?= (int)($fleetState['inactive'] ?? 0) ?>],
            backgroundColor: ['#09BC8A', '#F59E0B', '#EF4444']
        }]
    },
    options: chartOptions
});
</script>