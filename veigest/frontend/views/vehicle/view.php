<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\models\Vehicle $model */

$this->title = $model->license_plate;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Veículos', 'url' => ['dashboard/vehicles']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <?= Html::encode($this->title) ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/vehicles']) ?>">Veículos</a></li>
                        <li class="breadcrumb-item active"><?= Html::encode($this->title) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10">
                    <div class="card" style="box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="card-header" style="background-color: var(--primary-color); color: white; border-bottom: 3px solid var(--dark-color);">
                            <h3 class="card-title">
                                <i class="fas fa-car mr-2"></i>Detalhes do Veículo
                            </h3>
                            <div class="card-tools">
                                <?php if (Yii::$app->user->can('vehicles.update')) { ?>
                                    <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
                                <?php } ?>
                                <?php if (Yii::$app->user->can('vehicles.delete')) { ?>
                                    <?= Html::a('<i class="fas fa-trash"></i> Apagar', ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-danger btn-sm',
                                        'data' => [
                                            'confirm' => 'Tem certeza que deseja apagar este veículo?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="card-body p-5">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    'license_plate',
                                    'brand',
                                    'model',
                                    'year',
                                    'fuel_type',
                                    'mileage',
                                    [
                                        'attribute' => 'status',
                                        'value' => function($model) {
                                            $statuses = ['ativo' => '<span class="badge bg-success">Ativo</span>', 'inativo' => '<span class="badge bg-secondary">Inativo</span>', 'manutencao' => '<span class="badge bg-warning">Manutenção</span>'];
                                            return $statuses[$model->status] ?? $model->status;
                                        },
                                        'format' => 'html',
                                    ],
                                    'driver_id',
                                    [
                                        'attribute' => 'created_at',
                                        'value' => function($model) {
                                            return date('d/m/Y H:i:s', strtotime($model->created_at));
                                        },
                                    ],
                                    [
                                        'attribute' => 'updated_at',
                                        'value' => function($model) {
                                            return date('d/m/Y H:i:s', strtotime($model->updated_at));
                                        },
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
