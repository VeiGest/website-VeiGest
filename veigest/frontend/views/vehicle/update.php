<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Vehicle $model */

$this->title = 'Editar Veículo: ' . $model->license_plate;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Veículos', 'url' => ['dashboard/vehicles']];
$this->params['breadcrumbs'][] = ['label' => $model->license_plate, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        Editar Veículo
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/vehicles']) ?>">Veículos</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>"><?= Html::encode($model->license_plate) ?></a></li>
                        <li class="breadcrumb-item active">Editar</li>
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
                                <i class="fas fa-edit mr-2"></i>Editar: <?= Html::encode($model->license_plate) ?>
                            </h3>
                        </div>
                        <div class="card-body p-5">
                            <?= $this->render('_form', [
                                'model' => $model,
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
