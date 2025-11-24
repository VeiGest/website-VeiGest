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
                <h3>245</h3>
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
                <h3>182</h3>
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
                <h3 style="color: white;">12</h3>
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
                <h3 style="color: white;">€45K</h3>
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
                    <div class="list-group-item">
                        <i class="fas fa-calendar-times text-danger mr-2"></i>
                        <strong>Documento Vencido</strong>
                        <p class="text-muted mb-0">Seguro do veículo ABC-1234</p>
                    </div>
                    <div class="list-group-item">
                        <i class="fas fa-wrench text-warning mr-2"></i>
                        <strong>Manutenção Programada</strong>
                        <p class="text-muted mb-0">Revisão vence em 5 dias</p>
                    </div>
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
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        datasets: [{
            label: 'Consumo (L)',
            data: [12500, 13200, 12800, 13500, 14200, 13800, 14500, 15000, 14300, 13900, 14600, 15200],
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
            data: [200, 35, 10],
            backgroundColor: ['#09BC8A', '#F59E0B', '#EF4444']
        }]
    },
    options: chartOptions
});
</script>