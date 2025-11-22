<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Dashboard';

$identity = Yii::$app->user->identity;
$displayName = !empty($identity->nome) ? $identity->nome : (!empty($identity->username) ? $identity->username : 'Utilizador');

?>
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Dashboard</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPI CARDS -->
        <div class="row mb-4">

            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>245</h3><p>Total Veículos</p>
                    </div>
                    <div class="icon"><i class="fas fa-truck"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>182</h3><p>Condutores Ativos</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-tie"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 style="color:white;">12</h3>
                        <p style="color:white;">Alertas Pendentes</p>
                    </div>
                    <div class="icon"><i class="fas fa-bell"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 style="color:white;">€45K</h3>
                        <p style="color:white;">Custo Mensal</p>
                    </div>
                    <div class="icon"><i class="fas fa-euro-sign"></i></div>
                </div>
            </div>

        </div>

        <!-- CHARTS -->
        <div class="row">

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Consumo de Combustível</h3></div>
                    <div class="card-body"><canvas id="consumptionChart"></canvas></div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Estado da Frota</h3></div>
                    <div class="card-body"><canvas id="stateChart"></canvas></div>
                </div>
            </div>

        </div>

        <!-- LISTAS -->
        <div class="row mt-4">

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Alertas Recentes</h3></div>
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
                    <div class="card-header"><h3 class="card-title">Atividades Recentes</h3></div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item">
                                <strong><?= Html::encode($displayName) ?></strong> criou novo veículo
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

    </div>
</section>

<!-- CHART.JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
    const chartOptions = { responsive:true, maintainAspectRatio:false };

    // Consumo
    const ctxC = document.getElementById('consumptionChart')?.getContext('2d');
    if (ctxC) {
        new Chart(ctxC, {
            type:'line',
            data:{
                labels:['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
                datasets:[{
                    label:'Consumo (L)',
                    data:[12500,13200,12800,13500,14200,13800,14500,15000,14300,13900,14600,15200],
                    borderColor:'#09BC8A',
                    backgroundColor:'rgba(9,188,138,0.1)',
                    fill:true,
                    tension:0.4
                }]
            },
            options:chartOptions
        });
    }

    // Estado
    const ctxS = document.getElementById('stateChart')?.getContext('2d');
    if (ctxS) {
        new Chart(ctxS, {
            type:'doughnut',
            data:{
                labels:['Ativo','Manutenção','Inativo'],
                datasets:[{ data:[200,35,10], backgroundColor:['#09BC8A','#F59E0B','#EF4444'] }]
            },
            options:chartOptions
        });
    }
</script>
